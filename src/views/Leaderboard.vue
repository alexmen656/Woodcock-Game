<template>
  <div class="leaderboard-view">
    <div class="leaderboard-header">
      <h1>🏆 Leaderboard</h1>
      <p class="subtitle">Die besten Nest-Bauer der Welt</p>
    </div>
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Lade Leaderboard...</p>
    </div>
    <div v-else-if="error" class="error-message">
      <span class="error-icon">⚠️</span>
      <p>{{ error }}</p>
      <button @click="fetchLeaderboard" class="btn-retry">Erneut versuchen</button>
    </div>
    <div v-else class="leaderboard-container">
      <div class="stats-overview">
        <div class="stat-box">
          <span class="stat-number">{{ totalPlayers }}</span>
          <span class="stat-label">Spieler</span>
        </div>
        <div class="stat-box">
          <span class="stat-number">{{ onlinePlayers }}</span>
          <span class="stat-label">Online</span>
        </div>
        <div class="stat-box">
          <span class="stat-number">{{ totalGames }}</span>
          <span class="stat-label">Spiele</span>
        </div>
      </div>

      <div class="leaderboard-table">
        <div class="table-header">
          <div class="col-rank">Rang</div>
          <div class="col-player">Spieler</div>
          <div class="col-stats">Stats</div>
          <div class="col-points">Punkte</div>
        </div>
        <div 
          v-for="player in leaderboard" 
          :key="player.id"
          class="table-row"
          :class="{ 
            'top-three': player.rank <= 3,
            'is-online': player.is_online
          }"
        >
          <div class="col-rank">
            <div class="rank-badge" :class="`rank-${player.rank}`">
              <span v-if="player.rank <= 3" class="medal">
                {{ player.rank === 1 ? '🥇' : player.rank === 2 ? '🥈' : '🥉' }}
              </span>
              <span v-else class="rank-number">#{{ player.rank }}</span>
            </div>
          </div>
          <div class="col-player">
            <div class="player-info">
              <div class="player-avatar">🐦</div>
              <div class="player-details">
                <div class="player-name">
                  {{ player.username }}
                  <span v-if="player.is_online" class="online-dot" title="Online"></span>
                </div>
                <div class="player-meta">
                  {{ player.games_played }} Spiele gespielt
                </div>
              </div>
            </div>
          </div>
          <div class="col-stats">
            <div class="stats-mini">
              <span class="stat-item" title="Nest Level">
                🪹 {{ player.nest_level }}
              </span>
              <span class="stat-item" title="Eier">
                🥚 {{ player.eggs }}
              </span>
              <span class="stat-item" title="Dekorationen">
                ✨ {{ player.decorations }}
              </span>
            </div>
          </div>
          <div class="col-points">
            <div class="points-display">
              <span class="points-value">{{ formatNumber(player.total_points) }}</span>
              <span class="points-label">Punkte</span>
            </div>
            <div class="highscore-badge" v-if="player.highscore">
              Highscore: {{ player.highscore }}
            </div>
          </div>
        </div>
      </div>
      <div class="pagination" v-if="totalPages > 1">
        <button 
          @click="previousPage" 
          :disabled="currentPage === 1"
          class="btn-page"
        >
          ← Zurück
        </button>
        <span class="page-info">
          Seite {{ currentPage }} von {{ totalPages }}
        </span>
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="btn-page"
        >
          Weiter →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { LeaderboardAPI } from '../services/api'

const leaderboard = ref([])
const loading = ref(true)
const error = ref(null)
const totalPlayers = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(20)

const onlinePlayers = computed(() => {
  return leaderboard.value.filter(p => p.is_online).length
})

const totalGames = computed(() => {
  return leaderboard.value.reduce((sum, p) => sum + (p.games_played || 0), 0)
})

const totalPages = computed(() => {
  return Math.ceil(totalPlayers.value / itemsPerPage.value)
})

onMounted(() => {
  fetchLeaderboard()
})

async function fetchLeaderboard() {
  loading.value = true
  error.value = null
  
  try {
    const offset = (currentPage.value - 1) * itemsPerPage.value
    const response = await LeaderboardAPI.getLeaderboard(itemsPerPage.value, offset)
    
    if (response.success) {
      leaderboard.value = response.data
      totalPlayers.value = response.total
    } else {
      throw new Error('Failed to load leaderboard')
    }
  } catch (e) {
    error.value = 'Leaderboard konnte nicht geladen werden. Bitte versuche es später erneut.'
    console.error('Leaderboard error:', e)
  } finally {
    loading.value = false
  }
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    fetchLeaderboard()
  }
}

function previousPage() {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchLeaderboard()
  }
}

function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

<style scoped>
.leaderboard-view {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.leaderboard-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.leaderboard-header h1 {
  font-size: 2.5rem;
  color: var(--primary);
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.subtitle {
  font-size: 1.125rem;
  color: var(--text-secondary);
}

.loading {
  text-align: center;
  padding: 4rem 2rem;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  text-align: center;
  padding: 3rem 2rem;
  background: #fee;
  border: 1px solid #fcc;
  border-radius: var(--radius);
}

.error-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-retry:hover {
  background: #dd7730;
  transform: translateY(-1px);
}

.stats-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-box {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem;
  text-align: center;
  box-shadow: var(--shadow-sm);
}

.stat-number {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--accent);
  margin-bottom: 0.5rem;
}

.stat-label {
  color: var(--text-secondary);
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}

.leaderboard-table {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.table-header,
.table-row {
  display: grid;
  grid-template-columns: 80px 1fr 200px 150px;
  gap: 1rem;
  padding: 1rem 1.5rem;
  align-items: center;
}

.table-header {
  background: var(--bg-main);
  border-bottom: 2px solid var(--border);
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-row {
  border-bottom: 1px solid var(--border);
  transition: all 0.2s ease;
}

.table-row:hover {
  background: var(--bg-main);
}

.table-row:last-child {
  border-bottom: none;
}

.table-row.top-three {
  background: linear-gradient(90deg, rgba(237, 137, 54, 0.05), transparent);
}

.rank-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  font-weight: 700;
  font-size: 1.125rem;
}

.rank-badge.rank-1 {
  background: linear-gradient(135deg, #ffd700, #ffed4e);
  box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
}

.rank-badge.rank-2 {
  background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
  box-shadow: 0 4px 12px rgba(192, 192, 192, 0.3);
}

.rank-badge.rank-3 {
  background: linear-gradient(135deg, #cd7f32, #e8a87c);
  box-shadow: 0 4px 12px rgba(205, 127, 50, 0.3);
}

.rank-badge .medal {
  font-size: 1.75rem;
}

.rank-badge .rank-number {
  color: var(--text-secondary);
}

.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.player-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, var(--accent), #dd7730);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.player-details {
  flex: 1;
}

.player-name {
  font-weight: 600;
  color: var(--primary);
  font-size: 1.125rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.online-dot {
  width: 10px;
  height: 10px;
  background: #4caf50;
  border-radius: 50%;
  display: inline-block;
  animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.player-meta {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.stats-mini {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.stat-item {
  font-size: 0.9375rem;
  color: var(--text-secondary);
  font-weight: 600;
}

.points-display {
  text-align: right;
}

.points-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--accent);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.points-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.highscore-badge {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-top: 0.25rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1rem;
}

.btn-page {
  padding: 0.75rem 1.5rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--primary);
}

.btn-page:hover:not(:disabled) {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
  transform: translateY(-1px);
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-weight: 600;
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .leaderboard-view {
    padding: 1rem;
  }

  .leaderboard-header h1 {
    font-size: 2rem;
  }

  .table-header,
  .table-row {
    grid-template-columns: 60px 1fr;
    gap: 0.75rem;
    padding: 0.875rem;
  }

  .col-stats {
    grid-column: 1 / -1;
    margin-top: 0.5rem;
  }

  .col-points {
    grid-column: 1 / -1;
    text-align: left;
    margin-top: 0.5rem;
  }

  .points-display {
    text-align: left;
  }

  .stats-overview {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>