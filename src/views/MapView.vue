<template>
  <div class="map-view">
    <div class="map-container">
      <canvas 
        ref="mapCanvasRef" 
        :width="canvasWidth"
        :height="canvasHeight"
        @mousedown="handleMouseDown"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseLeave"
        @wheel="handleWheel"
        class="map-canvas"
      ></canvas>
      <div class="map-header-floating">
        <h1>Woodcock Forest</h1>
      </div>
      <div class="map-legend-floating">
        <div class="legend-item">
          <div class="legend-marker own"></div>
          <span>Your Nest</span>
        </div>
       <!-- <div class="legend-item">
          <div class="legend-marker friend"></div>
          <span>Friends</span>
        </div>-->
        <div class="legend-item">
          <div class="legend-marker other"></div>
          <span>Other Players</span>
        </div>
        <div class="legend-item">
          <div class="legend-marker tree"></div>
          <span>Trees</span>
        </div>
      </div>
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
            <span>Eggs:</span>
            <strong>{{ hoveredNest.eggs }}</strong>
          </div>
          <div class="tooltip-stat">
            <span>Points:</span>
            <strong>{{ hoveredNest.totalPoints }}</strong>
          </div>
        </div>
      </div>
    </div>
    <transition name="slide">
      <div v-if="selectedNest" class="nest-detail-panel">
        <button @click="closeDetail" class="btn-close">✕</button>
        <div class="detail-content">
          <div class="detail-header">
                <div class="player-avatar">
                <img src="../assets/logo.png" alt="Player" class="avatar-logo" />
              </div>
            <div class="detail-info">
              <h3>{{ selectedNest.username }}</h3>
              <span class="detail-rank">Rank #{{ selectedNest.rank }}</span>
            </div>
          </div>
          <div class="detail-stats-grid">
            <div class="detail-stat">
              <span class="detail-label">Nest Level</span>
              <span class="detail-value">{{ selectedNest.nestLevel }}/10</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Eggs</span>
              <span class="detail-value">{{ selectedNest.eggs }}/5</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Decorations</span>
              <span class="detail-value">{{ selectedNest.decorations }}/8</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Points</span>
              <span class="detail-value">{{ selectedNest.totalPoints }}</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Highscore</span>
              <span class="detail-value">{{ selectedNest.highscore || 0 }}</span>
            </div>
            <div class="detail-stat">
              <span class="detail-label">Games Played</span>
              <span class="detail-value">{{ selectedNest.gamesPlayed || 0 }}</span>
            </div>
          </div>
         <!--<canvas 
            ref="nestPreviewRef" 
            width="300"
            height="300" 
            class="nest-preview"
          ></canvas>-->
          <div v-if="selectedNest.isOwn" class="detail-actions">
            <button class="btn-action btn-visit" @click="visitNest">
              {{ selectedNest.isOwn ? 'Go to Nest' : 'Visit Nest' }}
            </button>
            <!--<button v-if="!selectedNest.isOwn" class="btn-action btn-friend" @click="addFriend">
              {{ selectedNest.isFriend ? '✓ Freund' : '+ Freund hinzufügen' }}
            </button>-->
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
import axios from 'axios'

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

const camera = ref({
  x: 0,
  y: 0,
  zoom: 1,
  minZoom: 0.3,
  maxZoom: 2
})

const isDragging = ref(false)
const hasDragged = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const lastMousePos = ref({ x: 0, y: 0 })
const dragThreshold = 5
const worldWidth = computed(() => Math.max(3000, mockNests.value.length * 200))
const worldHeight = computed(() => Math.max(2000, mockNests.value.length * 150))

const mockNests = ref([])
//const isFullscreen = ref(false)

async function loadNests() {
  try {
    const response = await axios.get('https://alex.polan.sk/woodcock-game/api/map.php', {
      params: {
        action: 'getNests'
      }
    })
    
    if (response.data.success && response.data.data) {

      const ownNest = {
        id: 'own',
        owner: gameStore.username.value,
        username: gameStore.username.value,
        x: 1500,
        y: 1000,
        nestLevel: gameStore.nestLevel.value,
        eggs: gameStore.eggs.value,
        decorations: gameStore.decorations.value,
        totalPoints: gameStore.totalPoints.value,
        highscore: gameStore.highscore?.value || 0,
        gamesPlayed: gameStore.gamesPlayed?.value || 0,
        isOwn: true,
        isFriend: false,
        rank: 1
      }
      
      const nests = response.data.data.map(nest => ({
        id: nest.id.toString(),
        username: nest.owner,
        x: nest.x,
        y: nest.y,
        nestLevel: nest.nestLevel,
        eggs: nest.eggs,
        decorations: nest.decorations,
        totalPoints: nest.totalPoints,
        highscore: nest.highscore || 0,
        gamesPlayed: nest.games_played || 0,
        rank: nest.rank,
        isOwn: nest.owner === gameStore.username.value,
        isFriend: false
      }))
      
      mockNests.value = [ownNest, ...nests.filter(n => n.username !== gameStore.username.value)]
      console.log('Loaded nests from backend:', mockNests.value.length)
    }
  } catch (error) {
    console.error('Failed to load nests:', error)
    mockNests.value = [{
      id: 'own',
      username: gameStore.username.value,
      x: 1500,
      y: 1000,
      nestLevel: gameStore.nestLevel.value,
      eggs: gameStore.eggs.value,
      decorations: gameStore.decorations.value,
      totalPoints: gameStore.totalPoints.value,
      highscore: gameStore.highscore?.value || 0,
      gamesPlayed: gameStore.gamesPlayed?.value || 0,
      isOwn: true,
      isFriend: false,
      rank: 1
    }]
  }
}

const totalNests = computed(() => mockNests.value.length)
const onlineUsers = computed(() => Math.floor(mockNests.value.length * 0.7))

const trees = ref([])

onMounted(async () => {
  setupCanvas()
  await loadNests()
  trees.value = generateTrees(worldWidth.value, worldHeight.value, 50)
  centerOnOwnNest()
  drawMap()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

function generateTrees(width, height, count) {
  const treesArray = []
  for (let i = 0; i < count; i++) {
    treesArray.push({
      x: Math.random() * width,
      y: Math.random() * height,
      size: 0.8 + Math.random() * 0.6
    })
  }
  return treesArray
}

function centerOnOwnNest() {
  const ownNest = mockNests.value.find(n => n.isOwn)
  if (ownNest) {
    camera.value.x = ownNest.x - canvasWidth.value / 2
    camera.value.y = ownNest.y - canvasHeight.value / 2
  }
}

function setupCanvas() {
  const canvas = mapCanvasRef.value
  if (!canvas) return
  mapCtx = canvas.getContext('2d')
  
  handleResize()
  
  // Draw immediately after setup
  requestAnimationFrame(() => {
    if (mockNests.value.length > 0) {
      drawMap()
    }
  })
}

function worldToScreen(worldX, worldY) {
  return {
    x: (worldX - camera.value.x) * camera.value.zoom,
    y: (worldY - camera.value.y) * camera.value.zoom
  }
}

function screenToWorld(screenX, screenY) {
  return {
    x: screenX / camera.value.zoom + camera.value.x,
    y: screenY / camera.value.zoom + camera.value.y
  }
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
  ctx.save()
  
  const visibleArea = {
    left: camera.value.x - 100,
    right: camera.value.x + canvas.width / camera.value.zoom + 100,
    top: camera.value.y - 100,
    bottom: camera.value.y + canvas.height / camera.value.zoom + 100
  }
  
  ctx.strokeStyle = 'rgba(139, 119, 101, 0.3)'
  ctx.lineWidth = 20 * camera.value.zoom
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  
  const pathPoints = [
    { x: 300, y: 500 },
    { x: 800, y: 400 },
    { x: 1200, y: 700 },
    { x: 1600, y: 500 },
    { x: 2000, y: 800 },
    { x: 2400, y: 600 },
    { x: 2800, y: 900 }
  ]
  
  for (let i = 0; i < pathPoints.length - 1; i++) {
    const p1 = pathPoints[i]
    const p2 = pathPoints[i + 1]
    const pos1 = worldToScreen(p1.x, p1.y)
    const pos2 = worldToScreen(p2.x, p2.y)
    
    ctx.beginPath()
    ctx.moveTo(pos1.x, pos1.y)
    
    const midX = (p1.x + p2.x) / 2 + (i % 2 === 0 ? 150 : -150)
    const midY = (p1.y + p2.y) / 2 + (i % 3 === 0 ? 100 : -100)
    const midScreen = worldToScreen(midX, midY)
    
    ctx.quadraticCurveTo(midScreen.x, midScreen.y, pos2.x, pos2.y)
    ctx.stroke()
  }
  
  const verticalPaths = [
    [{ x: 600, y: 300 }, { x: 700, y: 1000 }],
    [{ x: 1500, y: 200 }, { x: 1400, y: 1200 }],
    [{ x: 2200, y: 400 }, { x: 2100, y: 1400 }]
  ]
  
  verticalPaths.forEach(path => {
    const p1 = path[0]
    const p2 = path[1]
    const pos1 = worldToScreen(p1.x, p1.y)
    const pos2 = worldToScreen(p2.x, p2.y)
    
    ctx.beginPath()
    ctx.moveTo(pos1.x, pos1.y)
    const midX = (p1.x + p2.x) / 2 + 100
    const midY = (p1.y + p2.y) / 2
    const midScreen = worldToScreen(midX, midY)
    ctx.quadraticCurveTo(midScreen.x, midScreen.y, pos2.x, pos2.y)
    ctx.stroke()
  })
  
  trees.value.forEach(tree => {
    if (tree.x >= visibleArea.left && tree.x <= visibleArea.right &&
        tree.y >= visibleArea.top && tree.y <= visibleArea.bottom) {
      const screenPos = worldToScreen(tree.x, tree.y)
      drawTree(ctx, screenPos.x, screenPos.y, tree.size * camera.value.zoom)
    }
  })
  
  mockNests.value.forEach(nest => {
    if (nest.x >= visibleArea.left && nest.x <= visibleArea.right &&
        nest.y >= visibleArea.top && nest.y <= visibleArea.bottom) {
      const screenPos = worldToScreen(nest.x, nest.y)
      drawMapNest(ctx, nest, screenPos.x, screenPos.y)
    }
  })
  
  ctx.restore()
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

function drawMapNest(ctx, nest, screenX, screenY) {
  const size = (30 + nest.nestLevel * 3) * camera.value.zoom
  
  ctx.save()
  
  ctx.fillStyle = 'rgba(0, 0, 0, 0.2)'
  ctx.beginPath()
  ctx.ellipse(screenX, screenY + size/2 + 5, size/2 + 5, 8, 0, 0, Math.PI * 2)
  ctx.fill()
  
  let nestColor = '#a0826d'
  if (nest.isOwn) nestColor = '#ed8936'
  else if (nest.isFriend) nestColor = '#4299e1'
  
  ctx.fillStyle = nestColor
  ctx.beginPath()
  ctx.arc(screenX, screenY, size/2, 0, Math.PI * 2)
  ctx.fill()
  
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)'
  ctx.lineWidth = 3
  ctx.stroke()
  
  ctx.strokeStyle = '#8B4513'
  ctx.lineWidth = 2
  for (let i = 0; i < 8; i++) {
    const angle = (i / 8) * Math.PI * 2
    ctx.beginPath()
    ctx.moveTo(screenX, screenY)
    ctx.lineTo(screenX + Math.cos(angle) * size/2, screenY + Math.sin(angle) * size/2)
    ctx.stroke()
  }
  
  ctx.fillStyle = 'white'
  ctx.font = `bold ${Math.max(10, 14 * camera.value.zoom)}px system-ui`
  ctx.textAlign = 'center'
  ctx.textBaseline = 'middle'
  ctx.fillText(nest.nestLevel, screenX, screenY)
  
  if (hoveredNest.value?.id === nest.id) {
    ctx.strokeStyle = '#ffd700'
    ctx.lineWidth = 4
    ctx.beginPath()
    ctx.arc(screenX, screenY, size/2 + 8, 0, Math.PI * 2)
    ctx.stroke()
  }
  
  if (selectedNest.value?.id === nest.id) {
    ctx.strokeStyle = '#ff6b35'
    ctx.lineWidth = 5
    ctx.setLineDash([5, 5])
    ctx.beginPath()
    ctx.arc(screenX, screenY, size/2 + 12, 0, Math.PI * 2)
    ctx.stroke()
    ctx.setLineDash([])
  }
  
  ctx.restore()
}

function handleMouseDown(event) {
  const canvas = mapCanvasRef.value
  
  isDragging.value = true
  hasDragged.value = false
  dragStart.value = { x: event.clientX, y: event.clientY }
  lastMousePos.value = { x: event.clientX, y: event.clientY }
}

function handleMouseMove(event) {
  const canvas = mapCanvasRef.value
  const rect = canvas.getBoundingClientRect()
  const mouseX = event.clientX - rect.left
  const mouseY = event.clientY - rect.top
  
  if (isDragging.value) {
    const totalDragX = Math.abs(event.clientX - dragStart.value.x)
    const totalDragY = Math.abs(event.clientY - dragStart.value.y)
    
    if (totalDragX > dragThreshold || totalDragY > dragThreshold) {
      hasDragged.value = true
      canvas.style.cursor = 'grabbing'
      
      const deltaX = event.clientX - lastMousePos.value.x
      const deltaY = event.clientY - lastMousePos.value.y
      
      camera.value.x -= deltaX / camera.value.zoom
      camera.value.y -= deltaY / camera.value.zoom
      
      lastMousePos.value = { x: event.clientX, y: event.clientY }
      
      hoveredNest.value = null
      drawMap()
    }
  } else {
    const worldPos = screenToWorld(mouseX, mouseY)
    let found = null
    
    for (const nest of mockNests.value) {
      const distance = Math.sqrt((worldPos.x - nest.x) ** 2 + (worldPos.y - nest.y) ** 2)
      const nestSize = (30 + nest.nestLevel * 3) / 2
      if (distance < nestSize) {
        found = nest
        tooltipX.value = event.clientX + 15
        tooltipY.value = event.clientY + 15
        break
      }
    }
    
    if (hoveredNest.value?.id !== found?.id) {
      hoveredNest.value = found
      canvas.style.cursor = found ? 'pointer' : 'grab'
      drawMap()
    }
  }
}

function handleMouseUp(event) {
  const canvas = mapCanvasRef.value
  
  if (isDragging.value && !hasDragged.value) {
    const rect = canvas.getBoundingClientRect()
    const mouseX = event.clientX - rect.left
    const mouseY = event.clientY - rect.top
    
    const worldPos = screenToWorld(mouseX, mouseY)
    
    for (const nest of mockNests.value) {
      const distance = Math.sqrt((worldPos.x - nest.x) ** 2 + (worldPos.y - nest.y) ** 2)
      const nestSize = (30 + nest.nestLevel * 3) / 2
      if (distance < nestSize) {
        selectedNest.value = nest
        drawMap()
        setTimeout(() => drawNestPreview(), 50)
        break
      }
    }
  }
  
  isDragging.value = false
  hasDragged.value = false
  
  if (canvas) {
    canvas.style.cursor = hoveredNest.value ? 'pointer' : 'grab'
  }
}

function handleMouseLeave(event) {
  if (isDragging.value) {
    isDragging.value = false
    hasDragged.value = false
  }
  hoveredNest.value = null
  const canvas = mapCanvasRef.value
  if (canvas) {
    canvas.style.cursor = 'grab'
  }
  drawMap()
}

function handleWheel(event) {
  event.preventDefault()
  
  const canvas = mapCanvasRef.value
  const rect = canvas.getBoundingClientRect()
  const mouseX = event.clientX - rect.left
  const mouseY = event.clientY - rect.top
  
  const worldPosBefore = screenToWorld(mouseX, mouseY)
  
  const zoomDelta = event.deltaY > 0 ? 0.9 : 1.1
  camera.value.zoom = Math.max(
    camera.value.minZoom,
    Math.min(camera.value.maxZoom, camera.value.zoom * zoomDelta)
  )
  
  const worldPosAfter = screenToWorld(mouseX, mouseY)
  
  camera.value.x += worldPosBefore.x - worldPosAfter.x
  camera.value.y += worldPosBefore.y - worldPosAfter.y
  
  drawMap()
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
  camera.value.zoom = Math.min(camera.value.zoom * 1.2, camera.value.maxZoom)
  drawMap()
}

function zoomOut() {
  camera.value.zoom = Math.max(camera.value.zoom * 0.8, camera.value.minZoom)
  drawMap()
}

function centerMap() {
  centerOnOwnNest()
  drawMap()
}

function toggleFullscreen() {
  isFullscreen.value = !isFullscreen.value
}*/

function handleResize() {
  const maxWidth = window.innerWidth
  const maxHeight = window.innerHeight - 120
  canvasWidth.value = maxWidth
  canvasHeight.value = maxHeight
  drawMap()
}
</script>

<style scoped>
main.app-main {
  padding: none !important
}
.map-view {
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  position: relative;
  overflow: hidden;
}

.map-container {
  position: relative;
  width: 100%;
  height: 100%;
  background: var(--bg-card);
  overflow: hidden;
}

.map-canvas {
  display: block;
  width: 100%;
  height: 100%;
  cursor: grab;
}

.map-header-floating {
  position: absolute;
  top: .75rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 100;
  background: rgba(255, 255, 255, 0.95);
  padding: 0.375rem 1rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border);
  backdrop-filter: blur(10px);
}

.map-header-floating h1 {
  font-size: 1.75rem;
  color: var(--primary);
  margin: 0;
  font-weight: 700;
  text-align: center;
}

.map-legend-floating {
  position: absolute;
  bottom: 1.5rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 1.5rem;
  z-index: 100;
  background: rgba(255, 255, 255, 0.95);
  padding: 0.75rem 1.5rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border);
  backdrop-filter: blur(10px);
  flex-wrap: wrap;
  justify-content: center;
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

.nest-detail-panel {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 400px;
  max-height: calc(100% - 2rem);
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
  margin: 0;
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
  gap: .75rem;
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
  transform: translateX(100%);
  opacity: 0;
}

.slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

@media (max-width: 1024px) {
  .nest-detail-panel {
    right: 1rem;
    left: 1rem;
    width: auto;
    max-width: 500px;
    margin: 0 auto;
    max-height: calc(100% - 2rem);
  }
}

@media (max-width: 768px) {
  .map-view {
    height: calc(100vh - 80px);
  }

  .map-header-floating {
    top: 0.75rem;
    padding: 0.5rem 1.25rem;
  }

  .map-header-floating h1 {
    font-size: 1.25rem;
  }

  .map-legend-floating {
    bottom: 0.75rem;
    padding: 0.5rem 1rem;
    gap: 1rem;
  }

  .legend-item {
    font-size: 0.75rem;
  }

  .legend-marker {
    width: 16px;
    height: 16px;
  }

  .nest-detail-panel {
    position: absolute;
    top: auto;
    bottom: 0;
    right: 0;
    left: 0;
    transform: none;
    border-radius: var(--radius) var(--radius) 0 0;
    max-height: 70%;
    width: 100%;
    padding: 1.5rem;
  }

  .detail-stats-grid {
    grid-template-columns: 1fr 1fr;
  }

  .slide-enter-from {
    transform: translateY(100%);
  }

  .slide-leave-to {
    transform: translateY(100%);
  }
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
  overflow: hidden;
}

.avatar-logo {
  width: 75%;
  height: auto;
  object-fit: cover;
}
</style>