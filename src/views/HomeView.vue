<template>
    <div class="home-view">
        <div class="nest-display">
            <h2>🪹 Your Nest</h2>
            <canvas ref="nestCanvasRef" width="400" height="400" class="nest-canvas"></canvas>
            <div class="nest-stats">
                <div class="stat-card">
                    <div class="stat-icon">🪹</div>
                    <div class="stat-info">
                        <span class="stat-label">Nest Level</span>
                        <span class="stat-value">{{ nestLevel }} / 10</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🍂</div>
                    <div class="stat-info">
                        <span class="stat-label">Collected Points</span>
                        <span class="stat-value">{{ totalPoints }}</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🥚</div>
                    <div class="stat-info">
                        <span class="stat-label">Eggs in Nest</span>
                        <span class="stat-value">{{ eggs }}</span>
                    </div>
                </div>
            </div>
            <div class="upgrades-section">
                <h3>Nest Upgrades</h3>
                <div class="upgrade-grid">
                    <div class="upgrade-card" :class="{ locked: totalPoints < nestUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>🪹 Expand Nest</h4>
                            <span class="upgrade-level">Level {{ nestLevel }}/10</span>
                        </div>
                        <p class="upgrade-desc">Expand your nest and add more branches</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ nestUpgradeCost }} Points</span>
                            <button @click="upgradeNest" :disabled="totalPoints < nestUpgradeCost || nestLevel >= 10"
                                class="btn-upgrade">
                                {{ nestLevel >= 10 ? 'Max Level' : 'Upgrade' }}
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-card" :class="{ locked: totalPoints < eggUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>🥚 Add Egg</h4>
                            <span class="upgrade-level">{{ eggs }}/5 Eggs</span>
                        </div>
                        <p class="upgrade-desc">Add another egg to your nest</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ eggUpgradeCost }} Points</span>
                            <button @click="upgradeEgg" :disabled="totalPoints < eggUpgradeCost || eggs >= 5"
                                class="btn-upgrade">
                                {{ eggs >= 5 ? 'Max Eggs' : 'Add' }}
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-card" :class="{ locked: totalPoints < decorationUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>✨ Decorations</h4>
                            <span class="upgrade-level">{{ decorations }}/8 Decorations</span>
                        </div>
                        <p class="upgrade-desc">Decorate your nest with flowers and feathers</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ decorationUpgradeCost }} Points</span>
                            <button @click="upgradeDecoration"
                                :disabled="totalPoints < decorationUpgradeCost || decorations >= 8" class="btn-upgrade">
                                {{ decorations >= 8 ? 'Max Decorations' : 'Add' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="play-section">
                <div class="action-buttons">
                    <button @click="goToGame" class="btn-play">
                        Go Collect Leaves!
                    </button>
                    <button @click="goToMap" class="btn-map">
                        To World Map
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from '../store/gameStore'

const router = useRouter()
const gameStore = useGameStore()

const nestCanvasRef = ref(null)
let nestCtx = null

const nestLevel = computed(() => {
    const val = gameStore.nestLevel.value
    return typeof val === 'number' ? val : 0
})
const eggs = computed(() => {
    const val = gameStore.eggs.value
    return typeof val === 'number' ? val : 0
})
const decorations = computed(() => {
    const val = gameStore.decorations.value
    return typeof val === 'number' ? val : 0
})
const totalPoints = computed(() => {
    const val = gameStore.totalPoints.value
    return typeof val === 'number' ? val : 0
})

const nestUpgradeCost = computed(() => {
    const level = nestLevel.value
    return (level + 1) * 100
})
const eggUpgradeCost = computed(() => {
    const eggCount = eggs.value
    return (eggCount + 1) * 150
})
const decorationUpgradeCost = computed(() => {
    const decoCount = decorations.value
    return (decoCount + 1) * 80
})

onMounted(() => {
    setupCanvas()
    setTimeout(() => {
        drawNest()
    }, 100)
})

watch([nestLevel, eggs, decorations], () => {
    if (nestCtx) {
        drawNest()
    }
})

function setupCanvas() {
    const canvas = nestCanvasRef.value
    if (!canvas) return
    nestCtx = canvas.getContext('2d')
}

function drawNest() {
    if (!nestCtx) return

    const canvas = nestCanvasRef.value
    const ctx = nestCtx

    ctx.clearRect(0, 0, canvas.width, canvas.height)

    const gradient = ctx.createRadialGradient(200, 200, 50, 200, 200, 200)
    gradient.addColorStop(0, '#fef4e8')
    gradient.addColorStop(1, '#fdd9b5')
    ctx.fillStyle = gradient
    ctx.fillRect(0, 0, canvas.width, canvas.height)

    const centerX = 200
    const centerY = 220
    const baseSize = 100 + nestLevel.value * 15

    const twigCount = 12 + nestLevel.value * 3
    ctx.strokeStyle = '#8B4513'
    ctx.lineWidth = 4

    for (let i = 0; i < twigCount; i++) {
        const angle = (i / twigCount) * Math.PI * 2
        const length = baseSize / 2 + Math.sin(i * 3) * 15
        const startX = centerX
        const startY = centerY
        const endX = startX + Math.cos(angle) * length
        const endY = startY + Math.sin(angle) * length

        ctx.beginPath()
        ctx.moveTo(startX, startY)
        ctx.lineTo(endX, endY)
        ctx.stroke()
    }

    ctx.fillStyle = '#A0522D'
    ctx.beginPath()
    ctx.arc(centerX, centerY, baseSize / 3, 0, Math.PI * 2)
    ctx.fill()

    ctx.strokeStyle = '#654321'
    ctx.lineWidth = 2
    for (let i = 0; i < 8; i++) {
        const angle = (i / 8) * Math.PI * 2
        const innerRadius = baseSize / 4
        const outerRadius = baseSize / 3
        ctx.beginPath()
        ctx.moveTo(centerX + Math.cos(angle) * innerRadius, centerY + Math.sin(angle) * innerRadius)
        ctx.lineTo(centerX + Math.cos(angle) * outerRadius, centerY + Math.sin(angle) * outerRadius)
        ctx.stroke()
    }

    const eggPositions = [
        { x: 0, y: 5 },
        { x: -15, y: 8 },
        { x: 15, y: 8 },
        { x: -8, y: -5 },
        { x: 8, y: -5 }
    ]

    for (let i = 0; i < eggs.value; i++) {
        const pos = eggPositions[i]
        const eggX = centerX + pos.x
        const eggY = centerY + pos.y

        ctx.fillStyle = 'rgba(0, 0, 0, 0.2)'
        ctx.beginPath()
        ctx.ellipse(eggX + 2, eggY + 2, 10, 13, 0, 0, Math.PI * 2)
        ctx.fill()

        ctx.fillStyle = '#F5F5DC'
        ctx.beginPath()
        ctx.ellipse(eggX, eggY, 10, 13, 0, 0, Math.PI * 2)
        ctx.fill()

        ctx.fillStyle = '#D3D3D3'
        ctx.beginPath()
        ctx.arc(eggX - 3, eggY - 4, 2, 0, Math.PI * 2)
        ctx.arc(eggX + 2, eggY + 3, 1.5, 0, Math.PI * 2)
        ctx.fill()

        ctx.strokeStyle = '#C0C0C0'
        ctx.lineWidth = 1
        ctx.stroke()
    }

    const decoPositions = [
        { x: -80, y: -60, type: 'flower' },
        { x: 80, y: -60, type: 'feather' },
        { x: -90, y: 0, type: 'flower' },
        { x: 90, y: 0, type: 'feather' },
        { x: -70, y: 60, type: 'feather' },
        { x: 70, y: 60, type: 'flower' },
        { x: 0, y: -90, type: 'flower' },
        { x: 0, y: 80, type: 'feather' }
    ]

    for (let i = 0; i < decorations.value; i++) {
        const deco = decoPositions[i]
        const decoX = centerX + deco.x
        const decoY = centerY + deco.y

        if (deco.type === 'flower') {
            ctx.fillStyle = '#FFB6C1'
            for (let p = 0; p < 5; p++) {
                const angle = (p / 5) * Math.PI * 2
                ctx.beginPath()
                ctx.arc(decoX + Math.cos(angle) * 8, decoY + Math.sin(angle) * 8, 6, 0, Math.PI * 2)
                ctx.fill()
            }
            ctx.fillStyle = '#FFD700'
            ctx.beginPath()
            ctx.arc(decoX, decoY, 5, 0, Math.PI * 2)
            ctx.fill()
        } else {
            ctx.strokeStyle = '#9370DB'
            ctx.lineWidth = 3
            ctx.beginPath()
            ctx.moveTo(decoX, decoY)
            ctx.lineTo(decoX - 5, decoY - 15)
            ctx.stroke()

            ctx.fillStyle = '#9370DB'
            ctx.beginPath()
            ctx.ellipse(decoX - 5, decoY - 18, 4, 10, -0.3, 0, Math.PI * 2)
            ctx.fill()
        }
    }

    drawWoodcock(ctx, centerX - 30, centerY - 50)
}

function drawWoodcock(ctx, x, y) {
    ctx.save()
    ctx.fillStyle = '#8B6F47'
    ctx.beginPath()
    ctx.ellipse(x, y, 25, 20, 0, 0, Math.PI * 2)
    ctx.fill()

    ctx.fillStyle = '#A0826D'
    ctx.beginPath()
    ctx.ellipse(x + 15, y, 12, 15, 0.3, 0, Math.PI * 2)
    ctx.fill()

    ctx.fillStyle = '#8B6F47'
    ctx.beginPath()
    ctx.arc(x - 15, y - 10, 15, 0, Math.PI * 2)
    ctx.fill()

    ctx.fillStyle = '#CD853F'
    ctx.beginPath()
    ctx.moveTo(x - 15, y - 10)
    ctx.lineTo(x - 35, y - 12)
    ctx.lineTo(x - 35, y - 8)
    ctx.closePath()
    ctx.fill()

    ctx.fillStyle = 'black'
    ctx.beginPath()
    ctx.arc(x - 12, y - 13, 2, 0, Math.PI * 2)
    ctx.fill()

    ctx.restore()
}

function upgradeNest() {
    console.log('Upgrade Nest - Before:', {
        totalPoints: totalPoints.value,
        nestLevel: nestLevel.value,
        cost: nestUpgradeCost.value
    })
    
    if (totalPoints.value >= nestUpgradeCost.value && nestLevel.value < 10) {
        gameStore.spendPoints(nestUpgradeCost.value)
        gameStore.upgradeNest()
        playUpgradeSound()
        
        console.log('Upgrade Nest - After:', {
            totalPoints: totalPoints.value,
            nestLevel: nestLevel.value
        })
    }
}

function upgradeEgg() {
    console.log('Upgrade Egg - Before:', {
        totalPoints: totalPoints.value,
        eggs: eggs.value,
        cost: eggUpgradeCost.value
    })
    
    if (totalPoints.value >= eggUpgradeCost.value && eggs.value < 5) {
        gameStore.spendPoints(eggUpgradeCost.value)
        gameStore.addEgg()
        playUpgradeSound()
        
        console.log('Upgrade Egg - After:', {
            totalPoints: totalPoints.value,
            eggs: eggs.value
        })
    }
}

function upgradeDecoration() {
    console.log('Upgrade Decoration - Before:', {
        totalPoints: totalPoints.value,
        decorations: decorations.value,
        cost: decorationUpgradeCost.value
    })
    
    if (totalPoints.value >= decorationUpgradeCost.value && decorations.value < 8) {
        gameStore.spendPoints(decorationUpgradeCost.value)
        gameStore.addDecoration()
        playUpgradeSound()
        
        console.log('Upgrade Decoration - After:', {
            totalPoints: totalPoints.value,
            decorations: decorations.value
        })
    }
}

function playUpgradeSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)()
        const oscillator = audioContext.createOscillator()
        const gainNode = audioContext.createGain()

        oscillator.connect(gainNode)
        gainNode.connect(audioContext.destination)

        oscillator.frequency.value = 800
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3)
        oscillator.start(audioContext.currentTime)
        oscillator.stop(audioContext.currentTime + 0.3)
    } catch (e) {
        console.warn('Audio not supported')
    }
}

function goToGame() {
    router.push('/game')
}

function goToMap() {
    router.push('/map')
}
</script>

<style scoped>
.home-view {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0;
}

.nest-display {
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 2.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
}

.nest-display h2 {
    text-align: center;
    font-size: 1.875rem;
    color: var(--primary);
    margin-bottom: 1.75rem;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.nest-canvas {
    display: block;
    margin: 0 auto 2.5rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    background: #fafafa;
}

.nest-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--bg-main);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2.5rem;
    filter: grayscale(20%);
}

.stat-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.stat-label {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    line-height: 1;
}

.upgrades-section {
    margin-bottom: 2.5rem;
}

.upgrades-section h3 {
    font-size: 1.5rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.upgrade-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.25rem;
}

.upgrade-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.5rem;
    transition: all 0.2s ease;
}

.upgrade-card:hover:not(.locked) {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--accent);
}

.upgrade-card.locked {
    opacity: 0.6;
    background: var(--bg-main);
}

.upgrade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.upgrade-header h4 {
    font-size: 1.125rem;
    color: var(--primary);
    margin: 0;
    font-weight: 600;
}

.upgrade-level {
    font-size: 0.75rem;
    color: var(--text-secondary);
    background: var(--bg-main);
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    border: 1px solid var(--border);
}

.upgrade-desc {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
    line-height: 1.5;
}

.upgrade-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.upgrade-cost {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--accent);
}

.btn-upgrade {
    background: var(--accent);
    color: white;
    font-weight: 600;
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-upgrade:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
    background: #dd7730;
}

.btn-upgrade:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    background: var(--text-secondary);
}

.play-section {
  text-align: center;
  padding-top: 2.5rem;
  border-top: 1px solid var(--border);
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-play,
.btn-map {
  font-size: 1.125rem;
  font-weight: 600;
  padding: 1rem 2rem;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
}

.btn-play {
  background: var(--accent);
  color: white;
  flex: 1;
  min-width: 250px;
}

.btn-play:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  background: #dd7730;
}

.btn-map {
  background: var(--primary);
  color: white;
  flex: 1;
  min-width: 250px;
}

.btn-map:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  background: #1a202c;
}

@media (max-width: 768px) {
    .nest-display {
        padding: 1rem;
    }

    .nest-canvas {
        width: 100%;
        height: auto;
    }

    .nest-display h2 {
        font-size: 1.75rem;
    }

    .upgrade-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .btn-play {
        font-size: 1.125rem;
        padding: 0.875rem 2rem;
    }

    .stat-card {
        padding: 1rem;
    }
}
</style>