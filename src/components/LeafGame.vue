<template>
  <div class="game-container">
    <div class="game-ui">
      <div class="stats">
        <div class="stat-item">
          <span class="label">Score:</span>
          <span class="value">{{ score }}</span>
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
      <h2>Game Over!</h2>
      <p class="final-score">Final Score: {{ score }}</p>
      <p v-if="isNewHighscore" class="new-highscore">🎉 New Highscore! 🎉</p>
    </div>
    <div class="instructions">
      <p>🖱️ Maus bewegen oder klicken | ⌨️ Pfeiltasten (← →) oder A / D | 🎮 Leertaste zum Pausieren</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

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
const highscoreDate = ref(null)
const soundEnabled = ref(true)
const isNewHighscore = ref(false)

const basket = ref({
  x: 400,
  y: 550,
  width: 80,
  height: 60,
  speed: 8,
  velocityX: 0
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
  
  basket.value.y = maxHeight - 50
  basket.value.x = Math.min(basket.value.x, maxWidth - basket.value.width)
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
  
  basket.value.x = canvasWidth.value / 2 - basket.value.width / 2
  
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
  updateBasket()
  
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

function updateBasket() {
  if (keys.value.left) {
    basket.value.velocityX = -basket.value.speed
  } else if (keys.value.right) {
    basket.value.velocityX = basket.value.speed
  } else {
    basket.value.velocityX *= 0.8
  }
  
  basket.value.x += basket.value.velocityX
  
  if (basket.value.x < 0) basket.value.x = 0
  if (basket.value.x > canvasWidth.value - basket.value.width) {
    basket.value.x = canvasWidth.value - basket.value.width
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
    
    // Remove if out of bounds (missed)
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
      leaf.x + leaf.width > basket.value.x &&
      leaf.x < basket.value.x + basket.value.width &&
      leaf.y + leaf.height > basket.value.y &&
      leaf.y < basket.value.y + basket.value.height
    ) {
      score.value += 10
      leafs.value.splice(i, 1)
      
      addCollisionEffect(leaf.x + leaf.width / 2, leaf.y + leaf.height / 2)
      playSound('catch')
    }
  }
}

function addCollisionEffect(x, y) {
  collisionEffects.value.push({
    x,
    y,
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
  
  drawBasket(context)
  
  collisionEffects.value.forEach(effect => drawCollisionEffect(context, effect))
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

function drawBasket(context) {
  const b = basket.value
  
  context.fillStyle = '#8B4513'
  context.fillRect(b.x, b.y, b.width, b.height)
  
  context.strokeStyle = '#654321'
  context.lineWidth = 2
  for (let i = 0; i < b.width; i += 10) {
    context.beginPath()
    context.moveTo(b.x + i, b.y)
    context.lineTo(b.x + i, b.y + b.height)
    context.stroke()
  }
  
  context.strokeStyle = '#8B4513'
  context.lineWidth = 4
  context.beginPath()
  context.arc(b.x + b.width / 2, b.y, 20, Math.PI, 0)
  context.stroke()
}

function drawCollisionEffect(context, effect) {
  context.save()
  context.globalAlpha = effect.alpha
  context.fillStyle = '#FFD700'
  context.font = `${20 * effect.scale}px Arial`
  context.textAlign = 'center'
  context.fillText('+10', effect.x, effect.y)
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
  if (!gameStarted.value || gameOver.value || paused.value) return
  
  const rect = canvasRef.value.getBoundingClientRect()
  const mouseX = e.clientX - rect.left
  
  basket.value.x = mouseX - basket.value.width / 2
  
  if (basket.value.x < 0) basket.value.x = 0
  if (basket.value.x > canvasWidth.value - basket.value.width) {
    basket.value.x = canvasWidth.value - basket.value.width
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
  
  if (score.value > highscore.value) {
    highscore.value = score.value
    highscoreDate.value = new Date().toISOString()
    isNewHighscore.value = true
    saveHighscore()
    playSound('highscore')
  }
  
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId)
  }
}

function getRandomLeafColor() {
  const colors = ['#ff6b35', '#ff9a56', '#ffa500', '#ffcc00', '#8b4513', '#a0522d']
  return colors[Math.floor(Math.random() * colors.length)]
}

function loadHighscore() {
  try {
    const saved = localStorage.getItem('leafgame_highscore')
    const savedDate = localStorage.getItem('leafgame_highscore_date')
    if (saved) {
      highscore.value = parseInt(saved, 10)
    }
    if (savedDate) {
      highscoreDate.value = savedDate
    }
  } catch (e) {
    console.warn('Failed to load highscore:', e)
  }
}

function saveHighscore() {
  try {
    localStorage.setItem('leafgame_highscore', highscore.value.toString())
    localStorage.setItem('leafgame_highscore_date', highscoreDate.value)
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
}

.game-ui {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
}

.stat-item .label {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.stat-item .value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ff6b35;
}

.stat-item.lives .value {
  font-size: 1.25rem;
}

.controls {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-primary {
  background: linear-gradient(135deg, #ff6b35, #ff9a56);
  color: white;
}

.btn-secondary {
  background: linear-gradient(135deg, #4a90e2, #67b5ff);
  color: white;
}

.btn-sound {
  background: #f0f0f0;
  font-size: 1.25rem;
  padding: 0.75rem 1rem;
}

canvas {
  border: 4px solid #8B4513;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  background: white;
  cursor: crosshair;
  display: block;
}

.game-over {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(255, 255, 255, 0.95);
  padding: 2rem 3rem;
  border-radius: 16px;
  box-shadow: 0 12px 32px rgba(0,0,0,0.3);
  text-align: center;
  z-index: 10;
}

.game-over h2 {
  font-size: 2.5rem;
  color: #ff6b35;
  margin-bottom: 1rem;
}

.final-score {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.new-highscore {
  font-size: 1.25rem;
  color: #FFD700;
  font-weight: 700;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.instructions {
  text-align: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 8px;
  font-size: 0.875rem;
  color: #666;
  max-width: 600px;
}

@media (max-width: 768px) {
  .game-ui {
    flex-direction: column;
    align-items: stretch;
  }
  
  .stats {
    justify-content: space-around;
    gap: 1rem;
  }
  
  .controls {
    justify-content: center;
  }
  
  .game-over h2 {
    font-size: 2rem;
  }
  
  .final-score {
    font-size: 1.25rem;
  }
  
  .instructions {
    font-size: 0.75rem;
    padding: 0.75rem;
  }
}
</style>
