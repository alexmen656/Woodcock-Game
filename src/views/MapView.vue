<template>
  <div class="map-view">
    <div class="map-header">
      <h1>🌲 Woodcock Forest</h1>
      <p class="map-subtitle">Entdecke die Nester anderer Spieler</p>
      <div class="map-stats">
        <div class="map-stat">
          <span class="stat-icon">🪹</span>
          <span class="stat-text">{{ totalNests }} Nester</span>
        </div>
        <div class="map-stat">
          <span class="stat-icon">🐦</span>
          <span class="stat-text">{{ onlineUsers }} Online</span>
        </div>
      </div>
    </div>
    <div class="map-container">
      <canvas 
        ref="mapCanvasRef" 
        :width="canvasWidth"
        :height="canvasHeight"
        @click="handleMapClick"
        @mousemove="handleMouseMove"
        class="map-canvas"
      ></canvas>
      <div 
        v-if="hoveredNest" 
        class="nest-tooltip"
        :style="{ left: tooltipX + 'px', top: tooltipY + 'px' }"
      >
        <div class="tooltip-header">
          <span class="tooltip-icon">🪹</span>
          <span class="tooltip-name">{{ hoveredNest.username }}</span>
        </div>
        <div class="tooltip-stats">
          <div class="tooltip-stat">
            <span>Level:</span>
            <strong>{{ hoveredNest.nestLevel }}</strong>
          </div>
          <div class="tooltip-stat">
            <span>Eier:</span>
            <strong>{{ hoveredNest.eggs }}</strong>
          </div>
          <div class="tooltip-stat">
            <span>Punkte:</span>
            <strong>{{ hoveredNest.totalPoints }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="map-legend">
      <div class="legend-item">
        <div class="legend-marker own"></div>
        <span>Dein Nest</span>
      </div>
      <div class="legend-item">
        <div class="legend-marker friend"></div>
        <span>Freunde</span>
      </div>
      <div class="legend-item">
        <div class="legend-marker other"></div>
        <span>Andere Spieler</span>
      </div>
      <div class="legend-item">
        <div class="legend-marker tree"></div>
        <span>Bäume</span>
      </div>
    </div>
    <!--<div class="map-controls">
      <button @click="zoomIn" class="btn-control" title="Zoom In">
        <span>🔍+</span>
      </button>
      <button @click="zoomOut" class="btn-control" title="Zoom Out">
        <span>🔍-</span>
      </button>
      <button @click="centerMap" class="btn-control" title="Zentrieren">
        <span>🎯</span>
      </button>
      <button @click="toggleFullscreen" class="btn-control" title="Vollbild">
        <span>{{ isFullscreen ? '📐' : '⛶' }}</span>
      </button>
    </div>-->
    <transition name="slide">
      <div v-if="selectedNest" class="nest-detail-panel">
        <button @click="closeDetail" class="btn-close">✕</button>
        <div class="detail-content">
          <div class="detail-header">
            <div class="detail-avatar">🐦</div>
            <div class="detail-info">
              <h3>{{ selectedNest.username }}</h3>
              <span class="detail-rank">Rang #{{ selectedNest.rank }}</span>
            </div>
          </div>
          
          <div class="detail-stats-grid">
            <div class="detail-stat">
              <span class="detail-label">Nest Level</span>
              <span class="detail-value">{{ selectedNest.nestLevel }}/10</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Eier</span>
              <span class="detail-value">{{ selectedNest.eggs }}/5</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Dekorationen</span>
              <span class="detail-value">{{ selectedNest.decorations }}/8</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Punkte</span>
              <span class="detail-value">{{ selectedNest.totalPoints }}</span>
            </div>
          </div>
          <canvas 
            ref="nestPreviewRef" 
            width="300" 
            height="300" 
            class="nest-preview"
          ></canvas>
          <div class="detail-actions">
            <button class="btn-action btn-visit" @click="visitNest">
              Nest besuchen
            </button>
            <button v-if="!selectedNest.isOwn" class="btn-action btn-friend" @click="addFriend">
              {{ selectedNest.isFriend ? '✓ Freund' : '+ Freund hinzufügen' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '../store/gameStore'

const router = useRouter()
const gameStore = useGameStore()

const mapCanvasRef = ref(null)
const nestPreviewRef = ref(null)
const canvasWidth = ref(1200)
const canvasHeight = ref(800)
let mapCtx = null

const hoveredNest = ref(null)
const selectedNest = ref(null)
const tooltipX = ref(0)
const tooltipY = ref(0)
const zoom = ref(1)
const offsetX = ref(0)
const offsetY = ref(0)
const isFullscreen = ref(false)

//mock data
const mockNests = ref([
  {
    id: 'own',
    username: 'Du',
    x: 600,
    y: 400,
    nestLevel: gameStore.nestLevel.value,
    eggs: gameStore.eggs.value,
    decorations: gameStore.decorations.value,
    totalPoints: gameStore.totalPoints.value,
    isOwn: true,
    isFriend: false,
    rank: 1
  },
  {
    id: '1',
    username: 'WaldMeister',
    x: 450,
    y: 320,
    nestLevel: 7,
    eggs: 4,
    decorations: 6,
    totalPoints: 2100,
    isOwn: false,
    isFriend: true,
    rank: 2
  },
  {
    id: '2',
    username: 'BaumFreund',
    x: 780,
    y: 280,
    nestLevel: 5,
    eggs: 3,
    decorations: 4,
    totalPoints: 1500,
    isOwn: false,
    isFriend: true,
    rank: 5
  },
  {
    id: '3',
    username: 'NestBauer',
    x: 320,
    y: 520,
    nestLevel: 8,
    eggs: 5,
    decorations: 7,
    totalPoints: 2800,
    isOwn: false,
    isFriend: false,
    rank: 3
  },
  {
    id: '4',
    username: 'Vogel123',
    x: 900,
    y: 450,
    nestLevel: 4,
    eggs: 2,
    decorations: 3,
    totalPoints: 1200,
    isOwn: false,
    isFriend: false,
    rank: 8
  },
  {
    id: '5',
    username: 'FeatherKing',
    x: 550,
    y: 600,
    nestLevel: 9,
    eggs: 5,
    decorations: 8,
    totalPoints: 3200,
    isOwn: false,
    isFriend: false,
    rank: 1
  },
  {
    id: '6',
    username: 'TreeHugger',
    x: 200,
    y: 250,
    nestLevel: 3,
    eggs: 1,
    decorations: 2,
    totalPoints: 800,
    isOwn: false,
    isFriend: false,
    rank: 12
  }
])

const trees = ref([
  { x: 150, y: 150, size: 1.2 },
  { x: 350, y: 200, size: 0.9 },
  { x: 550, y: 180, size: 1.1 },
  { x: 750, y: 160, size: 1.0 },
  { x: 950, y: 200, size: 1.3 },
  { x: 1050, y: 350, size: 0.8 },
  { x: 250, y: 400, size: 1.0 },
  { x: 450, y: 550, size: 1.2 },
  { x: 650, y: 650, size: 0.9 },
  { x: 850, y: 600, size: 1.1 },
  { x: 1000, y: 700, size: 1.0 },
  { x: 150, y: 650, size: 1.2 }
])

const totalNests = computed(() => mockNests.value.length)
const onlineUsers = computed(() => Math.floor(mockNests.value.length * 0.7))

onMounted(() => {
  setupCanvas()
  drawMap()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

function setupCanvas() {
  const canvas = mapCanvasRef.value
  if (!canvas) return
  mapCtx = canvas.getContext('2d')
}

function drawMap() {
  if (!mapCtx) return
  
  const canvas = mapCanvasRef.value
  const ctx = mapCtx
  
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  
  const bgGradient = ctx.createLinearGradient(0, 0, 0, canvas.height)
  bgGradient.addColorStop(0, '#e8f4e8')
  bgGradient.addColorStop(0.5, '#d4e8d4')
  bgGradient.addColorStop(1, '#c0dcc0')
  ctx.fillStyle = bgGradient
  ctx.fillRect(0, 0, canvas.width, canvas.height)
  
  ctx.fillStyle = 'rgba(139, 195, 74, 0.1)'
  for (let i = 0; i < 50; i++) {
    const x = Math.random() * canvas.width
    const y = Math.random() * canvas.height
    ctx.fillRect(x, y, 3, 3)
  }
  
  ctx.strokeStyle = 'rgba(139, 119, 101, 0.3)'
  ctx.lineWidth = 20
  ctx.lineCap = 'round'
  ctx.beginPath()
  ctx.moveTo(100, 300)
  ctx.quadraticCurveTo(400, 350, 700, 300)
  ctx.quadraticCurveTo(900, 280, 1100, 400)
  ctx.stroke()
  
  trees.value.forEach(tree => {
    drawTree(ctx, tree.x, tree.y, tree.size)
  })
  
  mockNests.value.forEach(nest => {
    drawMapNest(ctx, nest)
  })
}

function drawTree(ctx, x, y, size = 1) {
  ctx.save()
  
  const trunkWidth = 15 * size
  const trunkHeight = 40 * size
  ctx.fillStyle = '#6d4c41'
  ctx.fillRect(x - trunkWidth/2, y, trunkWidth, trunkHeight)
  
  const crownRadius = 35 * size
  ctx.fillStyle = '#558b2f'
  
  ctx.fillStyle = 'rgba(0, 0, 0, 0.15)'
  ctx.beginPath()
  ctx.ellipse(x, y + trunkHeight + 5, crownRadius * 1.2, 10, 0, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.fillStyle = '#689f38'
  ctx.beginPath()
  ctx.arc(x - 15 * size, y - 10 * size, crownRadius, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.fillStyle = '#7cb342'
  ctx.beginPath()
  ctx.arc(x + 15 * size, y - 10 * size, crownRadius, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.fillStyle = '#8bc34a'
  ctx.beginPath()
  ctx.arc(x, y - 20 * size, crownRadius, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.restore()
}

function drawMapNest(ctx, nest) {
  const size = 30 + nest.nestLevel * 3
  const x = nest.x
  const y = nest.y
  
  ctx.save()
  
  ctx.fillStyle = 'rgba(0, 0, 0, 0.2)'
  ctx.beginPath()
  ctx.ellipse(x, y + size/2 + 5, size/2 + 5, 8, 0, 0, Math.PI * 2)
  ctx.fill()
  
  let nestColor = '#a0826d'
  if (nest.isOwn) nestColor = '#ed8936'
  else if (nest.isFriend) nestColor = '#4299e1'
  
  ctx.fillStyle = nestColor
  ctx.beginPath()
  ctx.arc(x, y, size/2, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)'
  ctx.lineWidth = 3
  ctx.stroke()
  
  ctx.strokeStyle = '#8B4513'
  ctx.lineWidth = 2
  for (let i = 0; i < 8; i++) {
    const angle = (i / 8) * Math.PI * 2
    ctx.beginPath()
    ctx.moveTo(x, y)
    ctx.lineTo(x + Math.cos(angle) * size/2, y + Math.sin(angle) * size/2)
    ctx.stroke()
  }
  
  ctx.fillStyle = 'white'
  ctx.font = 'bold 14px system-ui'
  ctx.textAlign = 'center'
  ctx.textBaseline = 'middle'
  ctx.fillText(nest.nestLevel, x, y)
  
  if (hoveredNest.value?.id === nest.id) {
    ctx.strokeStyle = '#ffd700'
    ctx.lineWidth = 4
    ctx.beginPath()
    ctx.arc(x, y, size/2 + 8, 0, Math.PI * 2)
    ctx.stroke()
  }
  
  if (selectedNest.value?.id === nest.id) {
    ctx.strokeStyle = '#ff6b35'
    ctx.lineWidth = 5
    ctx.setLineDash([5, 5])
    ctx.beginPath()
    ctx.arc(x, y, size/2 + 12, 0, Math.PI * 2)
    ctx.stroke()
    ctx.setLineDash([])
  }
  
  ctx.restore()
}

function handleMouseMove(event) {
  const canvas = mapCanvasRef.value
  const rect = canvas.getBoundingClientRect()
  const mouseX = event.clientX - rect.left
  const mouseY = event.clientY - rect.top
  
  let found = null
  for (const nest of mockNests.value) {
    const size = 30 + nest.nestLevel * 3
    const distance = Math.sqrt((mouseX - nest.x) ** 2 + (mouseY - nest.y) ** 2)
    if (distance < size/2) {
      found = nest
      tooltipX.value = event.clientX + 15
      tooltipY.value = event.clientY + 15
      break
    }
  }
  
  hoveredNest.value = found
  
  canvas.style.cursor = found ? 'pointer' : 'default'
  
  if (found) {
    drawMap()
  } else if (hoveredNest.value !== found) {
    drawMap()
  }
}

function handleMapClick(event) {
  if (hoveredNest.value) {
    selectedNest.value = hoveredNest.value
    drawMap()
    
    setTimeout(() => {
      drawNestPreview()
    }, 50)
  }
}

function drawNestPreview() {
  const canvas = nestPreviewRef.value
  if (!canvas || !selectedNest.value) return
  
  const ctx = canvas.getContext('2d')
  const centerX = 150
  const centerY = 150
  const nest = selectedNest.value
  
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  
  const gradient = ctx.createRadialGradient(centerX, centerY, 50, centerX, centerY, 150)
  gradient.addColorStop(0, '#fef4e8')
  gradient.addColorStop(1, '#fdd9b5')
  ctx.fillStyle = gradient
  ctx.fillRect(0, 0, canvas.width, canvas.height)
  
  const baseSize = 80 + nest.nestLevel * 8
  
  ctx.strokeStyle = '#8B4513'
  ctx.lineWidth = 3
  for (let i = 0; i < 12 + nest.nestLevel * 2; i++) {
    const angle = (i / (12 + nest.nestLevel * 2)) * Math.PI * 2
    const length = baseSize / 2
    ctx.beginPath()
    ctx.moveTo(centerX, centerY)
    ctx.lineTo(centerX + Math.cos(angle) * length, centerY + Math.sin(angle) * length)
    ctx.stroke()
  }
  
  ctx.fillStyle = '#A0522D'
  ctx.beginPath()
  ctx.arc(centerX, centerY, baseSize / 3, 0, Math.PI * 2)
  ctx.fill()
  
  const eggPositions = [
    { x: 0, y: 5 },
    { x: -15, y: 8 },
    { x: 15, y: 8 },
    { x: -8, y: -5 },
    { x: 8, y: -5 }
  ]
  
  for (let i = 0; i < nest.eggs; i++) {
    const pos = eggPositions[i]
    ctx.fillStyle = '#F5F5DC'
    ctx.beginPath()
    ctx.ellipse(centerX + pos.x, centerY + pos.y, 8, 10, 0, 0, Math.PI * 2)
    ctx.fill()
    ctx.strokeStyle = '#C0C0C0'
    ctx.lineWidth = 1
    ctx.stroke()
  }
  
  ctx.fillStyle = '#8B6F47'
  ctx.beginPath()
  ctx.ellipse(centerX - 25, centerY - 30, 20, 15, 0, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.fillStyle = 'black'
  ctx.beginPath()
  ctx.arc(centerX - 22, centerY - 33, 2, 0, Math.PI * 2)
  ctx.fill()
}

function closeDetail() {
  selectedNest.value = null
  drawMap()
}

function visitNest() {
  if (selectedNest.value?.isOwn) {
    router.push('/')
  } else {
    alert(`Nest von ${selectedNest.value?.username} besuchen - Feature kommt bald!`)
  }
}

function addFriend() {
  if (selectedNest.value && !selectedNest.value.isOwn) {
    selectedNest.value.isFriend = !selectedNest.value.isFriend
    drawMap()
  }
}

/*function zoomIn() {
  zoom.value = Math.min(zoom.value + 0.2, 2)
}

function zoomOut() {
  zoom.value = Math.max(zoom.value - 0.2, 0.5)
}

function centerMap() {
  offsetX.value = 0
  offsetY.value = 0
}

function toggleFullscreen() {
  isFullscreen.value = !isFullscreen.value
}*/

function handleResize() {
  drawMap()
}
</script>

<style scoped>
.map-view {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.map-header {
  text-align: center;
  margin-bottom: 2rem;
}

.map-header h1 {
  font-size: 2.5rem;
  color: var(--primary);
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.map-subtitle {
  font-size: 1.125rem;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
}

.map-stats {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.map-stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--bg-card);
  padding: 0.75rem 1.5rem;
  border-radius: var(--radius);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.stat-icon {
  font-size: 1.5rem;
}

.stat-text {
  font-weight: 600;
  color: var(--primary);
}

.map-container {
  position: relative;
  background: var(--bg-card);
  border-radius: var(--radius);
  padding: 1rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border);
  overflow: hidden;
}

.map-canvas {
  display: block;
  width: 100%;
  height: auto;
  border-radius: 8px;
}

.nest-tooltip {
  position: fixed;
  background: rgba(255, 255, 255, 0.98);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0.75rem;
  box-shadow: var(--shadow-lg);
  pointer-events: none;
  z-index: 1000;
  backdrop-filter: blur(8px);
  min-width: 200px;
}

.tooltip-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--border);
}

.tooltip-icon {
  font-size: 1.25rem;
}

.tooltip-name {
  font-weight: 600;
  color: var(--primary);
}

.tooltip-stats {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.tooltip-stat {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.tooltip-stat strong {
  color: var(--accent);
  font-weight: 600;
}

.map-legend {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 1.5rem;
  padding: 1rem;
  background: var(--bg-card);
  border-radius: var(--radius);
  border: 1px solid var(--border);
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.legend-marker {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid white;
}

.legend-marker.own {
  background: var(--accent);
}

.legend-marker.friend {
  background: #4299e1;
}

.legend-marker.other {
  background: #a0826d;
}

.legend-marker.tree {
  background: #8bc34a;
}

.map-controls {
  position: absolute;
  bottom: 2rem;
  right: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.btn-control {
  width: 48px;
  height: 48px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-control:hover {
  background: white;
  border-color: var(--accent);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.nest-detail-panel {
  position: fixed;
  top: 50%;
  right: 2rem;
  transform: translateY(-50%);
  width: 400px;
  max-height: 90vh;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg);
  padding: 2rem;
  overflow-y: auto;
  z-index: 100;
}

.btn-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 32px;
  height: 32px;
  background: var(--bg-main);
  border: 1px solid var(--border);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
}

.btn-close:hover {
  background: #fee;
  border-color: #f44336;
  color: #f44336;
}

.detail-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.detail-avatar {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--accent), #dd7730);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  box-shadow: var(--shadow-sm);
}

.detail-info h3 {
  font-size: 1.5rem;
  color: var(--primary);
  margin: 0 0 0.25rem 0;
  font-weight: 700;
}

.detail-rank {
  font-size: 0.875rem;
  color: var(--text-secondary);
  background: var(--bg-main);
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  border: 1px solid var(--border);
  font-weight: 600;
}

.detail-stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.detail-stat {
  background: var(--bg-main);
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
}

.detail-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--accent);
}

.nest-preview {
  width: 100%;
  border-radius: 8px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.detail-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-action {
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9375rem;
}

.btn-visit {
  background: var(--accent);
  color: white;
}

.btn-visit:hover {
  background: #dd7730;
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.btn-friend {
  background: var(--bg-main);
  color: var(--primary);
  border: 1px solid var(--border);
}

.btn-friend:hover {
  background: #4299e1;
  color: white;
  border-color: #4299e1;
}

/* Slide Transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from {
  transform: translate(100%, -50%);
  opacity: 0;
}

.slide-leave-to {
  transform: translate(100%, -50%);
  opacity: 0;
}

@media (max-width: 1024px) {
  .nest-detail-panel {
    right: 1rem;
    left: 1rem;
    width: auto;
    max-width: 500px;
    margin: 0 auto;
  }

  .map-controls {
    right: 1rem;
    bottom: 1rem;
  }
}

@media (max-width: 768px) {
  .map-view {
    padding: 1rem;
  }

  .map-header h1 {
    font-size: 2rem;
  }

  .map-stats {
    gap: 1rem;
  }

  .map-legend {
    gap: 1rem;
  }

  .nest-detail-panel {
    position: fixed;
    top: auto;
    bottom: 0;
    right: 0;
    left: 0;
    transform: none;
    border-radius: var(--radius) var(--radius) 0 0;
    max-height: 80vh;
    width: 100%;
  }

  .slide-enter-from {
    transform: translateY(100%);
  }

  .slide-leave-to {
    transform: translateY(100%);
  }
}
</style>
