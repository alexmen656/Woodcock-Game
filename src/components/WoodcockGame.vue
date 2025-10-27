<template>
  <div class="game-container">
    <div class="game-ui">
      <div class="stats">
        <div class="stat-item">
          <span class="label">Current Points:</span>
          <span class="value">{{ score }}</span>
        </div>
        <div class="stat-item">
          <span class="label">Total Points:</span>
          <span class="value">{{ totalPoints }}</span>
        </div>
        <div class="stat-item">
          <span class="label">Highscore:</span>
          <span class="value">{{ highscore }}</span>
        </div>
        <div class="stat-item lives">
          <span class="label">Lives:</span>
          <span class="value">{{ '❤️'.repeat(lives) }}</span>
        </div>
      </div>
    
      <div class="controls">
        <button 
          v-if="!gameStarted || gameOver" 
          @click="startGame"
          class="btn-primary"
        >
          {{ gameOver ? 'Restart' : 'Play' }}
        </button>
        <button 
          v-if="gameStarted && !gameOver" 
          @click="togglePause"
          class="btn-secondary"
        >
          {{ paused ? 'Resume' : 'Pause' }}
        </button>
        <button
          v-if="gameOver"
          @click="backToNest"
          class="btn-back"
        >
          🪹 Zurück zum Nest
        </button>
        <button 
          @click="toggleSound"
          class="btn-sound"
          :title="soundEnabled ? 'Sound aus' : 'Sound an'"
        >
          {{ soundEnabled ? '🔊' : '🔇' }}
        </button>
      </div>
    </div>
    <canvas 
      ref="canvasRef" 
      :width="canvasWidth" 
      :height="canvasHeight"
      @mousemove="handleMouseMove"
      @click="handleCanvasClick"
    ></canvas>
    <div v-if="gameOver" class="game-over">
      <h2>Round Over!</h2>
      <p class="final-score">Collected: {{ score }} Points</p>
      <p class="total-points">Total: {{ totalPoints }} Points</p>
      <p v-if="isNewHighscore" class="new-highscore">🎉 New Highscore! 🎉</p>
      <p class="hint">Go back to the nest to buy upgrades!</p>
    </div>
    <div class="instructions">
      <p>🐦 Collect leaves for your nest! | 🖱️ Move mouse | ⌨️ Arrow keys (← →) or A / D | 🎮 Space = Pause</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '../store/gameStore'
import { LeaderboardAPI } from '../services/api'

const router = useRouter()
const gameStore = useGameStore()

const canvasRef = ref(null)
const canvasWidth = ref(800)
const canvasHeight = ref(600)
const ctx = ref(null)

const gameStarted = ref(false)
const gameOver = ref(false)
const paused = ref(false)
const score = ref(0)
const lives = ref(3)
const highscore = ref(0)
const soundEnabled = ref(true)
const isNewHighscore = ref(false)

const totalPoints = computed(() => {
    const val = gameStore.totalPoints.value
    return typeof val === 'number' ? val : 0
})

const woodcock = ref({
  x: 400,
  y: 500,
  width: 60,
  height: 50,
  speed: 8,
  velocityX: 0,
  wingAngle: 0,
  wingSpeed: 0.2
})

const leafs = ref([])
let leafSpawnTimer = 0
const leafSpawnInterval = 80

const keys = ref({
  left: false,
  right: false
})

let animationFrameId = null
let frameCount = 0

const collisionEffects = ref([])

onMounted(() => {
  setupCanvas()
  loadHighscore()
  window.addEventListener('keydown', handleKeyDown)
  window.addEventListener('keyup', handleKeyUp)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId)
  }
  window.removeEventListener('keydown', handleKeyDown)
  window.removeEventListener('keyup', handleKeyUp)
  window.removeEventListener('resize', handleResize)
})

function setupCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return
  
  ctx.value = canvas.getContext('2d')
  handleResize()
}

function handleResize() {
  const maxWidth = Math.min(800, window.innerWidth - 40)
  const maxHeight = Math.min(600, window.innerHeight - 300)
  canvasWidth.value = maxWidth
  canvasHeight.value = maxHeight
  
  woodcock.value.y = maxHeight - 100
  woodcock.value.x = Math.min(woodcock.value.x, maxWidth - woodcock.value.width)
}

function startGame() {
  gameStarted.value = true
  gameOver.value = false
  paused.value = false
  score.value = 0
  lives.value = 3
  leafs.value = []
  collisionEffects.value = []
  isNewHighscore.value = false
  frameCount = 0
  leafSpawnTimer = 0
  
  woodcock.value.x = canvasWidth.value / 2 - woodcock.value.width / 2
  
  gameLoop()
}

function gameLoop() {
  if (gameOver.value) return
  
  if (!paused.value) {
    update()
    draw()
    frameCount++
  } else {
    drawPauseScreen()
  }
  
  animationFrameId = requestAnimationFrame(gameLoop)
}

function update() {
  updateWoodcock()
  
  leafSpawnTimer++
  if (leafSpawnTimer >= leafSpawnInterval) {
    spawnLeaf()
    leafSpawnTimer = 0
  }
  
  updateLeafs()
  checkCollisions()
  updateCollisionEffects()
  
  if (lives.value <= 0) {
    endGame()
  }
}

function updateWoodcock() {
  if (keys.value.left) {
    woodcock.value.velocityX = -woodcock.value.speed
  } else if (keys.value.right) {
    woodcock.value.velocityX = woodcock.value.speed
  } else {
    woodcock.value.velocityX *= 0.8
  }
  
  woodcock.value.x += woodcock.value.velocityX
  
  woodcock.value.wingAngle += woodcock.value.wingSpeed
  
  if (woodcock.value.x < 0) woodcock.value.x = 0
  if (woodcock.value.x > canvasWidth.value - woodcock.value.width) {
    woodcock.value.x = canvasWidth.value - woodcock.value.width
  }
}

function spawnLeaf() {
  const leaf = {
    x: Math.random() * (canvasWidth.value - 40) + 20,
    y: -30,
    width: 30,
    height: 30,
    speed: 2 + Math.random() * 2,
    rotation: Math.random() * Math.PI * 2,
    rotationSpeed: (Math.random() - 0.5) * 0.1,
    color: getRandomLeafColor()
  }
  leafs.value.push(leaf)
}

function updateLeafs() {
  for (let i = leafs.value.length - 1; i >= 0; i--) {
    const leaf = leafs.value[i]
    leaf.y += leaf.speed
    leaf.rotation += leaf.rotationSpeed
    
    if (leaf.y > canvasHeight.value) {
      leafs.value.splice(i, 1)
      lives.value--
      playSound('miss')
    }
  }
}

function checkCollisions() {
  for (let i = leafs.value.length - 1; i >= 0; i--) {
    const leaf = leafs.value[i]
    
    if (
      leaf.x + leaf.width > woodcock.value.x &&
      leaf.x < woodcock.value.x + woodcock.value.width &&
      leaf.y + leaf.height > woodcock.value.y &&
      leaf.y < woodcock.value.y + woodcock.value.height
    ) {
      const points = 10
      score.value += points
      leafs.value.splice(i, 1)
      
      addCollisionEffect(leaf.x + leaf.width / 2, leaf.y + leaf.height / 2, points)
      playSound('catch')
    }
  }
}

function addCollisionEffect(x, y, points) {
  collisionEffects.value.push({
    x,
    y,
    points,
    scale: 1,
    alpha: 1,
    duration: 20,
    frame: 0
  })
}

function updateCollisionEffects() {
  for (let i = collisionEffects.value.length - 1; i >= 0; i--) {
    const effect = collisionEffects.value[i]
    effect.frame++
    effect.scale += 0.1
    effect.alpha -= 1 / effect.duration
    
    if (effect.frame >= effect.duration) {
      collisionEffects.value.splice(i, 1)
    }
  }
}

function draw() {
  const canvas = canvasRef.value
  const context = ctx.value
  if (!context) return
  
  context.clearRect(0, 0, canvas.width, canvas.height)
  
  const gradient = context.createLinearGradient(0, 0, 0, canvas.height)
  gradient.addColorStop(0, '#87ceeb')
  gradient.addColorStop(1, '#fef4e8')
  context.fillStyle = gradient
  context.fillRect(0, 0, canvas.width, canvas.height)
  
  leafs.value.forEach(leaf => drawLeaf(context, leaf))
  
  drawWoodcock(context)
  
  collisionEffects.value.forEach(effect => drawCollisionEffect(context, effect))
}

function drawWoodcock(context) {
  const w = woodcock.value
  
  context.save()
  context.translate(w.x + w.width / 2, w.y + w.height / 2)
  
  context.fillStyle = '#8B6F47'
  context.beginPath()
  context.ellipse(0, 0, w.width / 2, w.height / 2, 0, 0, Math.PI * 2)
  context.fill()
  
  const wingOffset = Math.sin(w.wingAngle) * 10
  
  context.fillStyle = '#A0826D'
  context.beginPath()
  context.ellipse(-w.width / 3, wingOffset, w.width / 4, w.height / 3, -0.3, 0, Math.PI * 2)
  context.fill()
  
  context.beginPath()
  context.ellipse(w.width / 3, wingOffset, w.width / 4, w.height / 3, 0.3, 0, Math.PI * 2)
  context.fill()
  
  context.fillStyle = '#8B6F47'
  context.beginPath()
  context.arc(-w.width / 4, -w.height / 4, w.width / 3, 0, Math.PI * 2)
  context.fill()
  
  context.fillStyle = '#CD853F'
  context.beginPath()
  context.moveTo(-w.width / 4, -w.height / 4)
  context.lineTo(-w.width / 2 - 15, -w.height / 4 - 5)
  context.lineTo(-w.width / 2 - 15, -w.height / 4 + 5)
  context.closePath()
  context.fill()
  
  context.fillStyle = 'black'
  context.beginPath()
  context.arc(-w.width / 4 + 5, -w.height / 4 - 5, 3, 0, Math.PI * 2)
  context.fill()
  
  context.fillStyle = '#8B6F47'
  context.beginPath()
  context.moveTo(w.width / 2, 0)
  context.lineTo(w.width / 2 + 15, -10)
  context.lineTo(w.width / 2 + 15, 10)
  context.closePath()
  context.fill()
  
  context.restore()
}

function drawLeaf(context, leaf) {
  context.save()
  context.translate(leaf.x + leaf.width / 2, leaf.y + leaf.height / 2)
  context.rotate(leaf.rotation)
  
  context.fillStyle = leaf.color
  context.beginPath()
  context.moveTo(0, -15)
  context.quadraticCurveTo(10, -5, 8, 10)
  context.quadraticCurveTo(0, 8, -8, 10)
  context.quadraticCurveTo(-10, -5, 0, -15)
  context.closePath()
  context.fill()
  
  context.strokeStyle = '#654321'
  context.lineWidth = 2
  context.beginPath()
  context.moveTo(0, -15)
  context.lineTo(0, 10)
  context.stroke()
  
  context.restore()
}

function drawCollisionEffect(context, effect) {
  context.save()
  context.globalAlpha = effect.alpha
  context.fillStyle = '#FFD700'
  context.font = `bold ${20 * effect.scale}px Arial`
  context.textAlign = 'center'
  context.fillText(`+${effect.points}`, effect.x, effect.y)
  context.restore()
}

function drawPauseScreen() {
  const context = ctx.value
  if (!context) return
  
  context.fillStyle = 'rgba(0, 0, 0, 0.5)'
  context.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
  
  context.fillStyle = 'white'
  context.font = '48px Arial'
  context.textAlign = 'center'
  context.fillText('PAUSED', canvasWidth.value / 2, canvasHeight.value / 2)
}

function handleKeyDown(e) {
  if (e.code === 'ArrowLeft' || e.code === 'KeyA') {
    keys.value.left = true
  }
  if (e.code === 'ArrowRight' || e.code === 'KeyD') {
    keys.value.right = true
  }
  if (e.code === 'Space' && gameStarted.value && !gameOver.value) {
    e.preventDefault()
    togglePause()
  }
}

function handleKeyUp(e) {
  if (e.code === 'ArrowLeft' || e.code === 'KeyA') {
    keys.value.left = false
  }
  if (e.code === 'ArrowRight' || e.code === 'KeyD') {
    keys.value.right = false
  }
}

function handleMouseMove(e) {
  if (!gameStarted.value || gameOver.value || paused.value || showNestUpgrade.value) return
  
  const rect = canvasRef.value.getBoundingClientRect()
  const mouseX = e.clientX - rect.left
  
  woodcock.value.x = mouseX - woodcock.value.width / 2
  
  if (woodcock.value.x < 0) woodcock.value.x = 0
  if (woodcock.value.x > canvasWidth.value - woodcock.value.width) {
    woodcock.value.x = canvasWidth.value - woodcock.value.width
  }
}

function handleCanvasClick() {
  if (!gameStarted.value) {
    startGame()
  }
}

function togglePause() {
  paused.value = !paused.value
}

function endGame() {
  gameOver.value = true
  gameStarted.value = false
  gameStore.addPoints(score.value)
  
  const isNewHS = gameStore.updateHighscore(score.value)
  if (isNewHS) {
    isNewHighscore.value = true
    playSound('highscore')
  }
  
  saveGameSession()
  
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId)
  }
}

async function saveGameSession() {
  try {
    const duration = Math.floor(frameCount / 60)
    const leavesCollected = Math.floor(score.value / 10)
    
    await gameStore.saveGameSession(score.value, leavesCollected, duration)
    
    console.log('Game session saved to backend')
  } catch (error) {
    console.warn('Failed to save game session:', error)
  }
}

function backToNest() {
  router.push('/')
}

function getRandomLeafColor() {
  const colors = ['#ff6b35', '#ff9a56', '#ffa500', '#ffcc00', '#8b4513', '#a0522d']
  return colors[Math.floor(Math.random() * colors.length)]
}

function loadHighscore() {
  try {
    const savedHighscore = localStorage.getItem('woodcock_game_highscore')
    
    if (savedHighscore) highscore.value = parseInt(savedHighscore, 10)
  } catch (e) {
    console.warn('Failed to load highscore:', e)
  }
}

function saveHighscore() {
  try {
    localStorage.setItem('woodcock_game_highscore', highscore.value.toString())
  } catch (e) {
    console.warn('Failed to save highscore:', e)
  }
}

function toggleSound() {
  soundEnabled.value = !soundEnabled.value
}

function playSound(type) {
  if (!soundEnabled.value) return
  
  try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)()
    const oscillator = audioContext.createOscillator()
    const gainNode = audioContext.createGain()
    
    oscillator.connect(gainNode)
    gainNode.connect(audioContext.destination)
    
    if (type === 'catch') {
      oscillator.frequency.value = 800
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1)
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.1)
    } else if (type === 'miss') {
      oscillator.frequency.value = 200
      gainNode.gain.setValueAtTime(0.2, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2)
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.2)
    } else if (type === 'upgrade') {
      oscillator.frequency.value = 1200
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4)
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.4)
    } else if (type === 'highscore') {
      oscillator.frequency.value = 1000
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3)
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.3)
    }
  } catch (e) {
    console.warn('Audio not supported:', e)
  }
}
</script>

<style scoped>
.game-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
  max-width: 900px;
  margin: 0 auto;
  position: relative;
  padding: 1rem;
}

.game-ui {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  background: var(--bg-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  flex-wrap: wrap;
  gap: 1.25rem;
}

.stats {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.stat-item .label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-item .value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--accent);
}

.stat-item.lives .value {
  font-size: 1.25rem;
}

.stat-item.nest .value {
  color: var(--primary);
}

.controls {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary,
.btn-back,
.btn-sound {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9375rem;
}

.btn-primary {
  background: var(--accent);
  color: white;
}

.btn-primary:hover {
  background: #dd7730;
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.btn-secondary {
  background: var(--primary);
  color: white;
}

.btn-secondary:hover {
  background: #1a202c;
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.btn-back {
  background: var(--primary);
  color: white;
}

.btn-back:hover {
  background: #1a202c;
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.btn-sound {
  background: var(--bg-main);
  font-size: 1.125rem;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border);
}

.btn-sound:hover {
  background: var(--bg-card);
  border-color: var(--accent);
}

canvas {
  border: 2px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-md);
  background: white;
  cursor: crosshair;
  display: block;
}

.game-over {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(255, 255, 255, 0.98);
  padding: 2.5rem 3rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border);
  text-align: center;
  z-index: 10;
  backdrop-filter: blur(8px);
}

.game-over h2 {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 1.25rem;
  font-weight: 700;
}

.final-score, .total-points {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary);
  margin: 0.75rem 0;
}

.total-points {
  color: var(--accent);
  font-size: 1.5rem;
}

.hint {
  font-size: 0.9375rem;
  color: var(--text-secondary);
  margin-top: 1.25rem;
}

.new-highscore {
  font-size: 1.125rem;
  color: var(--accent);
  font-weight: 700;
  animation: pulse 1s infinite;
  margin-top: 1rem;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.instructions {
  text-align: center;
  padding: 1rem 1.5rem;
  background: var(--bg-card);
  border-radius: var(--radius);
  border: 1px solid var(--border);
  font-size: 0.875rem;
  color: var(--text-secondary);
  max-width: 700px;
  line-height: 1.6;
}

.instructions p {
  margin: 0.25rem 0;
}

@media (max-width: 768px) {
  .game-container {
    padding: 0.75rem;
  }

  .game-ui {
    flex-direction: column;
    align-items: stretch;
    padding: 1rem;
    gap: 1rem;
  }
  
  .stats {
    justify-content: space-around;
    gap: 1rem;
  }
  
  .controls {
    justify-content: center;
  }
  
  .game-over {
    padding: 1.75rem 2rem;
  }

  .game-over h2 {
    font-size: 1.5rem;
  }
  
  .final-score, .total-points {
    font-size: 1.125rem;
  }
  
  .instructions {
    font-size: 0.8125rem;
    padding: 0.875rem 1.25rem;
  }

  canvas {
    max-width: 100%;
    height: auto;
  }
}
</style>