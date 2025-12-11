<template>
  <div class="leaderboard-view halloween-theme">
    <div class="leaderboard-header">
      <h1>Leaderboard</h1>
      <p class="subtitle">The most haunted Nest-Builders in the graveyard</p>
    </div>
    <div v-if="loading" class="loading">
      <div class="spinner halloween-spinner"></div>
      <p>Summoning the Leaderboard...</p>
    </div>
    <div v-else-if="error" class="error-message halloween-error">
      <span class="error-icon">💀</span>
      <p>{{ error }}</p>
      <button @click="fetchLeaderboard" class="btn-retry">Try Again</button>
    </div>
    <div v-else class="leaderboard-container">
      <div class="stats-overview">
        <div class="stat-box halloween-stat">
          <span class="stat-number">{{ totalPlayers }}</span>
          <span class="stat-label">Players</span>
        </div>
        <div class="stat-box halloween-stat">
          <span class="stat-number">{{ totalGames }}</span>
          <span class="stat-label">Games Played</span>
        </div>
      </div>
      <div class="leaderboard-table">
        <div class="table-header">
          <div class="col-rank">Rank</div>
          <div class="col-player">Players</div>
          <div class="col-stats">Stats</div>
          <div class="col-points">Points</div>
        </div>
        <div 
          v-for="player in leaderboard" 
          :key="player.id"
          class="table-row"
          :class="{ 
            'top-three': player.rank <= 3
          }"
        >
          <div class="col-rank">
            <div class="rank-badge" :class="`rank-${player.rank}`">
              <span v-if="player.rank <= 3" class="medal">
                {{ player.rank === 1 ? '👑' : player.rank === 2 ? '🎃' : '🕷️' }}
              </span>
              <span v-else class="rank-number">#{{ player.rank }}</span>
            </div>
          </div>
          <div class="col-player">
            <div class="player-info">
              <div class="player-avatar">
                <img src="../assets/logo.png" alt="Player" class="avatar-logo" />
              </div>
              <div class="player-details">
                <div class="player-name">
                  {{ player.username }}
                </div>
                <div class="player-meta">
                  🕸️ {{ player.games_played }} Games
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
                🎃 {{ player.decorations }}
              </span>
            </div>
          </div>
          <div class="col-points">
            <div class="points-display">
              <span class="points-value">{{ formatNumber(player.total_points) }}</span>
              <span class="points-label">Points</span>
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
          ← Back
        </button>
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="btn-page"
        >
          Next →
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

.halloween-theme {
  background: linear-gradient(180deg, #1a0b2e 0%, #16213e 100%);
  min-height: 100vh;
  position: relative;
}

.halloween-theme::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 30%, rgba(255, 110, 0, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(138, 43, 226, 0.1) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

.leaderboard-view > * {
  position: relative;
  z-index: 1;
}

.leaderboard-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.leaderboard-header h1 {
  font-size: 2.5rem;
  color: #ff6e00;
  margin-bottom: 0;
  font-weight: 700;
}

.subtitle {
  font-size: 1.125rem;
  color: #b19cd9;
}

.loading {
  text-align: center;
  padding: 4rem 2rem;
  color: #b19cd9;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(138, 43, 226, 0.3);
  border-top-color: #ff6e00;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

.halloween-spinner {
  border: 4px solid rgba(255, 110, 0, 0.3);
  border-top-color: #ff6e00;
  box-shadow: 0 0 20px rgba(255, 110, 0, 0.5);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  text-align: center;
  padding: 3rem 2rem;
  background: rgba(30, 10, 20, 0.8);
  border: 2px solid #8b0000;
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

.halloween-error {
  box-shadow: 0 0 30px rgba(139, 0, 0, 0.5);
  color: #ff6e00;
}

.error-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
  animation: shake 0.5s ease-in-out infinite;
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-10deg); }
  75% { transform: rotate(10deg); }
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #ff6e00, #ff4500);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(255, 110, 0, 0.4);
}

.btn-retry:hover {
  background: linear-gradient(135deg, #ff4500, #ff6e00);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 20px rgba(255, 110, 0, 0.6);
}

.stats-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-box {
  background: rgba(30, 10, 40, 0.6);
  border: 2px solid rgba(138, 43, 226, 0.4);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(138, 43, 226, 0.3);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.halloween-stat {
  background: linear-gradient(135deg, rgba(138, 43, 226, 0.2), rgba(255, 110, 0, 0.1));
  border: 2px solid rgba(255, 110, 0, 0.5);
  box-shadow: 0 4px 20px rgba(255, 110, 0, 0.3);
}

.halloween-stat:hover {
  transform: translateY(-3px) scale(1.01);
  box-shadow: 0 6px 20px rgba(255, 110, 0, 0.5);
  border-color: #ff6e00;
}

.stat-number {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  color: #ff6e00;
}

.stat-label {
  color: #b19cd9;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}

.leaderboard-table {
  background: rgba(20, 5, 30, 0.7);
  border: 2px solid rgba(138, 43, 226, 0.5);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(138, 43, 226, 0.4);
  backdrop-filter: blur(10px);
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
  background: rgba(138, 43, 226, 0.3);
  border-bottom: 2px solid rgba(255, 110, 0, 0.5);
  font-weight: 600;
  font-size: 0.875rem;
  color: #b19cd9;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-row {
  border-bottom: 1px solid rgba(138, 43, 226, 0.2);
  transition: all 0.3s ease;
}

.table-row:hover {
  background: rgba(255, 110, 0, 0.1);
  box-shadow: inset 0 0 20px rgba(255, 110, 0, 0.2);
  transform: scale(1.01);
}

.table-row:last-child {
  border-bottom: none;
}

.table-row.top-three {
  background: linear-gradient(90deg, rgba(255, 110, 0, 0.15), transparent);
  border-left: 3px solid #ff6e00;
  box-shadow: inset 0 0 20px rgba(255, 110, 0, 0.1);
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
  transition: all 0.3s ease;
}

.rank-badge.rank-1 {
  background: linear-gradient(135deg, #ff6e00, #ff4500);
  box-shadow: 0 4px 20px rgba(255, 110, 0, 0.6), 0 0 40px rgba(255, 110, 0, 0.4);
  animation: pulseCrown 2s ease-in-out infinite;
}

.rank-badge.rank-2 {
  background: linear-gradient(135deg, #8b00ff, #6a0dad);
  box-shadow: 0 4px 20px rgba(138, 43, 226, 0.6), 0 0 40px rgba(138, 43, 226, 0.4);
  animation: pulseBat 2s ease-in-out infinite;
}

.rank-badge.rank-3 {
  background: linear-gradient(135deg, #4b0082, #2e0854);
  box-shadow: 0 4px 20px rgba(75, 0, 130, 0.6), 0 0 40px rgba(75, 0, 130, 0.4);
  animation: pulseSpider 2s ease-in-out infinite;
}

@keyframes pulseCrown {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 4px 20px rgba(255, 110, 0, 0.6), 0 0 40px rgba(255, 110, 0, 0.4);
  }
  50% {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(255, 110, 0, 0.8), 0 0 50px rgba(255, 110, 0, 0.6);
  }
}

@keyframes pulseBat {
  0%, 100% {
    transform: scale(1) rotate(0deg);
  }
  50% {
    transform: scale(1.05) rotate(-5deg);
  }
}

@keyframes pulseSpider {
  0%, 100% {
    transform: scale(1) rotate(0deg);
  }
  50% {
    transform: scale(1.05) rotate(5deg);
  }
}

.rank-badge .medal {
  font-size: 1.75rem;
  filter: drop-shadow(0 0 8px rgba(255, 110, 0, 0.8));
}

.rank-badge .rank-number {
  color: #b19cd9;
}

.player-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.player-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #ff6e00, #8b00ff);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 0 20px rgba(255, 110, 0, 0.5);
  overflow: hidden;
  border: 2px solid rgba(138, 43, 226, 0.5);
  transition: all 0.3s ease;
}

.player-avatar:hover {
  transform: rotate(360deg) scale(1.1);
  box-shadow: 0 0 30px rgba(255, 110, 0, 0.8);
}

.avatar-logo {
  width: 75%;
  height: auto;
  object-fit: cover;
  filter: drop-shadow(0 0 5px rgba(255, 110, 0, 0.5));
}

.player-details {
  flex: 1;
}

.player-name {
  font-weight: 600;
  color: #ff6e00;
  font-size: 1.125rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.online-dot {
  width: 10px;
  height: 10px;
  background: #00ff00;
  border-radius: 50%;
  display: inline-block;
  animation: pulse-dot 2s infinite;
  box-shadow: 0 0 10px #00ff00;
}

@keyframes pulse-dot {
  0%, 100% { 
    opacity: 1; 
    box-shadow: 0 0 10px #00ff00;
  }
  50% { 
    opacity: 0.5;
    box-shadow: 0 0 20px #00ff00;
  }
}

.player-meta {
  font-size: 0.875rem;
  color: #b19cd9;
}

.stats-mini {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.stat-item {
  font-size: 0.9375rem;
  color: #b19cd9;
  font-weight: 600;
  transition: all 0.3s ease;
}

.stat-item:hover {
  color: #ff6e00;
  transform: scale(1.1);
}

.points-display {
  text-align: right;
}

.points-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #ff6e00;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.points-label {
  font-size: 0.75rem;
  color: #b19cd9;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.highscore-badge {
  font-size: 0.75rem;
  color: #8b00ff;
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
  background: linear-gradient(135deg, rgba(138, 43, 226, 0.3), rgba(255, 110, 0, 0.2));
  border: 2px solid rgba(255, 110, 0, 0.5);
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #ff6e00;
  box-shadow: 0 4px 15px rgba(255, 110, 0, 0.3);
  backdrop-filter: blur(10px);
}

.btn-page:hover:not(:disabled) {
  background: linear-gradient(135deg, #ff6e00, #ff4500);
  color: white;
  border-color: #ff6e00;
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 25px rgba(255, 110, 0, 0.5);
}

.btn-page:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  border-color: rgba(138, 43, 226, 0.3);
}

.page-info {
  font-weight: 600;
  color: #b19cd9;
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