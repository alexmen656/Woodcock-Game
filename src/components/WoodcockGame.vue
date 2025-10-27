<template>
  <div class="game-container">
    <div class="game-ui">
      <div class="stats">
        <div class="stat-item">
          <span class="label">Current Points</span>
          <span class="value">{{ score }}</span>
        </div>
        <div class="stat-item">
          <span class="label">Total Points</span>
          <span class="value">{{ totalPoints }}</span>
        </div>
        <div class="stat-item">
          <span class="label">Highscore</span>
          <span class="value">{{ highscore }}</span>
        </div>
        <div class="stat-item lives">
          <span class="label">Lives</span>
          <span class="value hearts">
            <svg v-for="n in lives" :key="n" class="heart-icon" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
          </span>
        </div>
      </div>

      <div class="controls">
        <button v-if="gameStarted && !gameOver" @click="togglePause" class="btn-secondary">
          {{ paused ? 'Resume' : 'Pause' }}
        </button>
        <button v-if="gameOver" @click="backToNest" class="btn-back">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </svg>
          Zurück zum Nest
        </button>
        <button @click="toggleSound" class="btn-sound" :title="soundEnabled ? 'Sound aus' : 'Sound an'">
          <svg v-if="soundEnabled" class="sound-icon" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z" />
          </svg>
          <svg v-else class="sound-icon" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z" />
          </svg>
        </button>
      </div>
    </div>

    <div class="canvas-wrapper">
      <canvas ref="canvasRef" :width="canvasWidth" :height="canvasHeight" @mousemove="handleMouseMove"
        @click="handleCanvasClick"></canvas>
      <div v-if="showTutorial" class="tutorial-overlay" @click="startGame">
        <div class="tutorial-content">
          <h2>Welcome to Nest Builder!</h2>
          <div class="tutorial-instructions">
           <!--<div class="tutorial-item">
              <svg class="tutorial-icon" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z" />
              </svg>
              <span>Move your mouse to control the bird</span>
            </div>--> 
            <div class="tutorial-item">
              <svg class="tutorial-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" />
              </svg>
              <span>Use ← → Arrow Keys or A / D</span>
            </div>
            <div class="tutorial-item">
              <svg class="tutorial-icon" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
              </svg>
              <span>Collect leaves to earn points</span>
            </div>
            <div class="tutorial-item">
              <svg class="tutorial-icon" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
              </svg>
              <span>Don't lose all hearts!</span>
            </div>
          </div>
          <button class="tutorial-btn">
            Click to Start
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
            </svg>
          </button>
        </div>
      </div>
      <div v-if="gameOver" class="game-over">
        <h2>Round Over!</h2>
        <p class="final-score">Collected: {{ score }} Points</p>
        <p class="total-points">Total: {{ totalPoints }} Points</p>
        <p v-if="isNewHighscore" class="new-highscore">🎉 New Highscore! 🎉</p>
        <p class="hint">Go back to the nest to buy upgrades!</p>
      </div>
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
const showTutorial = ref(true)
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
  showTutorial.value = false
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
  showTutorial.value = true
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
  gap: 1rem;
  max-width: 900px;
  margin: 0 auto;
  position: relative;
  /*padding: 1rem;*/
  height: calc(100vh - 120px);
  max-height: calc(100vh - 120px);
  overflow: hidden;
  box-sizing: border-box;
}

.game-ui {
  width: 800px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: var(--bg-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  flex-wrap: wrap;
  gap: 1rem;
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

.hearts {
  display: flex;
  gap: 0.25rem;
}

.heart-icon {
  width: 24px;
  height: 24px;
  color: #ef4444;
  filter: drop-shadow(0 2px 4px rgba(239, 68, 68, 0.3));
}

.stat-item.lives .value {
  font-size: 1.25rem;
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
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-icon {
  width: 20px;
  height: 20px;
}

.sound-icon {
  width: 20px;
  height: 20px;
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
  padding: 0.75rem 1rem;
  border: 1px solid var(--border);
}

.btn-sound:hover {
  background: var(--bg-card);
  border-color: var(--accent);
}

.canvas-wrapper {
  position: relative;
  width: 800px;
  height: 600px;
}

canvas {
  border: 2px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-md);
  background: white;
  cursor: crosshair;
  display: block;
  width: 800px;
  height: 600px;
}

.tutorial-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius);
  cursor: pointer;
  z-index: 10;
}

.tutorial-content {
  text-align: center;
  padding: 2rem;
  max-width: 600px;
}

.tutorial-content h2 {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 2rem;
  font-weight: 700;
}

.tutorial-instructions {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.tutorial-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: var(--bg-main);
  padding: 1rem 1.5rem;
  border-radius: var(--radius);
  border: 1px solid var(--border);
}

.tutorial-icon {
  width: 32px;
  height: 32px;
  color: var(--accent);
  flex-shrink: 0;
}

.tutorial-item span {
  font-size: 1rem;
  color: var(--primary);
  font-weight: 500;
  text-align: left;
}

.tutorial-btn {
  padding: 1rem 2.5rem;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-weight: 700;
  font-size: 1.125rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: var(--shadow-md);
}

.tutorial-btn:hover {
  background: #dd7730;
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.arrow-icon {
  width: 24px;
  height: 24px;
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

.final-score,
.total-points {
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

  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.05);
  }
}

@media (max-width: 900px) {
  .game-container {
    padding: 0.75rem;
  }

  .game-ui {
    width: 100%;
    max-width: 800px;
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

  .canvas-wrapper {
    width: 100%;
    max-width: 800px;
    height: auto;
    aspect-ratio: 4 / 3;
  }

  canvas {
    width: 100%;
    height: 100%;
  }

  .game-over {
    padding: 1.75rem 2rem;
  }

  .game-over h2 {
    font-size: 1.5rem;
  }

  .final-score,
  .total-points {
    font-size: 1.125rem;
  }

  .tutorial-content {
    padding: 1.5rem;
  }

  .tutorial-content h2 {
    font-size: 1.5rem;
  }
}
</style>