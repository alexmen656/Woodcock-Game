<template>
    <div class="home-view">
        <div class="nest-display">
            <h2>Your Nest</h2>
            <div class="nws">
                <canvas ref="nestCanvasRef" width="400" height="400" class="nest-canvas"></canvas>
                <div class="ns">
                    <div class="stat-card">
                        <div class="stat-icon">🪹</div>
                        <div class="stat-info">
                            <span class="stat-label">Nest Level</span>
                            <span class="stat-value">{{ nestLevel }} / 15</span>
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
            </div>
            <div class="upgrades-section">
                <h3>Nest Upgrades</h3>
                <div class="upgrade-grid">
                    <div class="upgrade-card" :class="{ locked: totalPoints < nestUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>🪹 Expand Nest</h4>
                            <span class="upgrade-level">Level {{ nestLevel }}/15</span>
                        </div>
                        <p class="upgrade-desc">Expand your nest and add more branches</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ nestUpgradeCost }} Points</span>
                            <button @click="upgradeNest" :disabled="totalPoints < nestUpgradeCost || nestLevel >= 15"
                                class="btn-upgrade">
                                {{ nestLevel >= 15 ? 'Max Level' : 'Upgrade' }}
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-card" :class="{ locked: totalPoints < eggUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>🥚 Add Egg</h4>
                            <span class="upgrade-level">{{ eggs }}/10 Eggs</span>
                        </div>
                        <p class="upgrade-desc">Add another egg to your nest</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ eggUpgradeCost }} Points</span>
                            <button @click="upgradeEgg" :disabled="totalPoints < eggUpgradeCost || eggs >= 10"
                                class="btn-upgrade">
                                {{ eggs >= 10 ? 'Max Eggs' : 'Add' }}
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-card" :class="{ locked: totalPoints < decorationUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>✨ Decorations</h4>
                            <span class="upgrade-level">{{ decorations }}/15 Decorations</span>
                        </div>
                        <p class="upgrade-desc">Decorate your nest with flowers and feathers</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ decorationUpgradeCost }} Points</span>
                            <button @click="upgradeDecoration"
                                :disabled="totalPoints < decorationUpgradeCost || decorations >= 15" class="btn-upgrade">
                                {{ decorations >= 15 ? 'Max Decorations' : 'Add' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="upgrades-section">
                <h3>Game Upgrades</h3>
                <div class="upgrade-grid">
                    <div class="upgrade-card" :class="{ locked: totalPoints < speedUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>⚡ Speed Boost</h4>
                            <span class="upgrade-level">Level {{ speedLevel }}/10</span>
                        </div>
                        <p class="upgrade-desc">Increase your movement speed in the game</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ speedUpgradeCost }} Points</span>
                            <button @click="upgradeSpeed"
                                :disabled="totalPoints < speedUpgradeCost || speedLevel >= 10" class="btn-upgrade">
                                {{ speedLevel >= 10 ? 'Max Speed' : 'Upgrade' }}
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-card" :class="{ locked: totalPoints < sizeUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>Size Increase</h4>
                            <span class="upgrade-level">Level {{ sizeLevel }}/10</span>
                        </div>
                        <p class="upgrade-desc">Make your bird bigger to catch more leaves</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ sizeUpgradeCost }} Points</span>
                            <button @click="upgradeSize"
                                :disabled="totalPoints < sizeUpgradeCost || sizeLevel >= 10" class="btn-upgrade">
                                {{ sizeLevel >= 10 ? 'Max Size' : 'Upgrade' }}
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-card" :class="{ locked: totalPoints < leafSizeUpgradeCost }">
                        <div class="upgrade-header">
                            <h4>🍂 Leaf Size</h4>
                            <span class="upgrade-level">Level {{ leafSizeLevel }}/10</span>
                        </div>
                        <p class="upgrade-desc">Increase the size of leaves for easier catching</p>
                        <div class="upgrade-footer">
                            <span class="upgrade-cost">{{ leafSizeUpgradeCost }} Points</span>
                            <button @click="upgradeLeafSize"
                                :disabled="totalPoints < leafSizeUpgradeCost || leafSizeLevel >= 10" class="btn-upgrade">
                                {{ leafSizeLevel >= 10 ? 'Max Size' : 'Upgrade' }}
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

const speedLevel = computed(() => {
    const val = gameStore.speedLevel.value
    return typeof val === 'number' ? val : 0
})

const sizeLevel = computed(() => {
    const val = gameStore.sizeLevel.value
    return typeof val === 'number' ? val : 0
})

const leafSizeLevel = computed(() => {
    const val = gameStore.leafSizeLevel.value
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

const speedUpgradeCost = computed(() => {
    const level = speedLevel.value
    return (level + 1) * 120
})

const sizeUpgradeCost = computed(() => {
    const level = sizeLevel.value
    return (level + 1) * 100
})

const leafSizeUpgradeCost = computed(() => {
    const level = leafSizeLevel.value
    return (level + 1) * 110
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
        { x: 8, y: -5 },
        { x: -20, y: 0 },
        { x: 20, y: 0 },
        { x: 0, y: -10 },
        { x: -12, y: 12 },
        { x: 12, y: 12 }
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
        { x: 0, y: 80, type: 'feather' },
        { x: -100, y: -30, type: 'flower' },
        { x: 100, y: -30, type: 'feather' },
        { x: -60, y: -80, type: 'feather' },
        { x: 60, y: -80, type: 'flower' },
        { x: -100, y: 30, type: 'flower' },
        { x: 100, y: 30, type: 'feather' },
        { x: -40, y: 90, type: 'flower' }
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
    
    if (totalPoints.value >= nestUpgradeCost.value && nestLevel.value < 15) {
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
    
    if (totalPoints.value >= eggUpgradeCost.value && eggs.value < 10) {
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
    
    if (totalPoints.value >= decorationUpgradeCost.value && decorations.value < 15) {
        gameStore.spendPoints(decorationUpgradeCost.value)
        gameStore.addDecoration()
        playUpgradeSound()
        
        console.log('Upgrade Decoration - After:', {
            totalPoints: totalPoints.value,
            decorations: decorations.value
        })
    }
}

function upgradeSpeed() {
    console.log('Upgrade Speed - Before:', {
        totalPoints: totalPoints.value,
        speedLevel: speedLevel.value,
        cost: speedUpgradeCost.value
    })
    
    if (totalPoints.value >= speedUpgradeCost.value && speedLevel.value < 10) {
        gameStore.spendPoints(speedUpgradeCost.value)
        gameStore.upgradeSpeed()
        playUpgradeSound()
        
        console.log('Upgrade Speed - After:', {
            totalPoints: totalPoints.value,
            speedLevel: speedLevel.value
        })
    }
}

function upgradeSize() {
    console.log('Upgrade Size - Before:', {
        totalPoints: totalPoints.value,
        sizeLevel: sizeLevel.value,
        cost: sizeUpgradeCost.value
    })
    
    if (totalPoints.value >= sizeUpgradeCost.value && sizeLevel.value < 10) {
        gameStore.spendPoints(sizeUpgradeCost.value)
        gameStore.upgradeSize()
        playUpgradeSound()
        
        console.log('Upgrade Size - After:', {
            totalPoints: totalPoints.value,
            sizeLevel: sizeLevel.value
        })
    }
}

function upgradeLeafSize() {
    console.log('Upgrade Leaf Size - Before:', {
        totalPoints: totalPoints.value,
        leafSizeLevel: leafSizeLevel.value,
        cost: leafSizeUpgradeCost.value
    })
    
    if (totalPoints.value >= leafSizeUpgradeCost.value && leafSizeLevel.value < 10) {
        gameStore.spendPoints(leafSizeUpgradeCost.value)
        gameStore.upgradeLeafSize()
        playUpgradeSound()
        
        console.log('Upgrade Leaf Size - After:', {
            totalPoints: totalPoints.value,
            leafSizeLevel: leafSizeLevel.value
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
    background: linear-gradient(180deg, #1a0b2e 0%, #2d1b4e 50%, #1a0b2e 100%);
    min-height: 100vh;
}

.nest-display {
    background: linear-gradient(135deg, rgba(26, 11, 46, 0.95), rgba(45, 27, 78, 0.95));
    border-radius: var(--radius);
    padding: 2.5rem;
    box-shadow: 
        0 8px 32px rgba(138, 43, 226, 0.4),
        0 0 40px rgba(255, 110, 0, 0.3),
        inset 0 0 20px rgba(0, 0, 0, 0.2);
    border: 2px solid rgba(255, 110, 0, 0.5);
    position: relative;
    overflow: hidden;
}

.nest-display::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(138, 43, 226, 0.1) 0%, transparent 70%);
    animation: spookyPulse 4s ease-in-out infinite;
    pointer-events: none;
}

@keyframes spookyPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.5;
    }
}

.nest-display h2 {
    text-align: center;
    font-size: 1.875rem;
    color: #ff6e00;
    margin-bottom: 1.75rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    text-shadow: 
        0 0 10px #ff6e00,
        0 0 20px #ff6e00,
        0 0 30px #ff4500;
    animation: spookyGlow 3s ease-in-out infinite;
}

@keyframes spookyGlow {
    0%, 100% {
        text-shadow: 
            0 0 10px #ff6e00,
            0 0 20px #ff6e00,
            0 0 30px #ff4500;
    }
    50% {
        text-shadow: 
            0 0 20px #ff6e00,
            0 0 30px #ff6e00,
            0 0 40px #ff4500,
            0 0 50px #ff4500;
    }
}

.nest-canvas {
    border: 2px solid #ff6e00;
    border-radius: var(--radius);
    box-shadow: 
        0 8px 32px rgba(138, 43, 226, 0.4),
        0 0 40px rgba(255, 110, 0, 0.3);
    background: linear-gradient(180deg, #2d1b4e 0%, #1a0b2e 100%);
}

.nws {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    justify-content: center;
    margin-bottom: 2.5rem;
    position: relative;
    z-index: 1;
}

.ns {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    min-width: 280px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: rgba(26, 11, 46, 0.8);
    border-radius: var(--radius);
    border: 2px solid rgba(255, 110, 0, 0.4);
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #ff6e00, #8b00ff, #ff6e00);
    border-radius: var(--radius);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
}

.stat-card:hover {
    box-shadow: 
        0 8px 32px rgba(138, 43, 226, 0.6),
        0 0 40px rgba(255, 110, 0, 0.4);
    transform: translateY(-2px);
    border-color: #ff6e00;
}

.stat-card:hover::before {
    opacity: 0.3;
}

.stat-icon {
    font-size: 2.5rem;
    filter: drop-shadow(0 0 10px rgba(255, 110, 0, 0.6));
}

.stat-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.stat-label {
    font-size: 0.8125rem;
    color: #b19cd9;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-shadow: 0 0 5px rgba(138, 43, 226, 0.5);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #ff6e00;
    line-height: 1;
    text-shadow: 0 0 10px rgba(255, 110, 0, 0.8);
}

.upgrades-section {
    margin-bottom: 2.5rem;
    position: relative;
    z-index: 1;
}

.upgrades-section h3 {
    font-size: 1.5rem;
    color: #ff6e00;
    margin-bottom: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    text-shadow: 0 0 10px rgba(255, 110, 0, 0.6);
    position: relative;
    display: inline-block;
}

.upgrade-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.25rem;
}

.upgrade-card {
    background: linear-gradient(135deg, rgba(26, 11, 46, 0.95), rgba(45, 27, 78, 0.95));
    border: 2px solid rgba(255, 110, 0, 0.3);
    border-radius: var(--radius);
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(139, 0, 255, 0.3);
}

.upgrade-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 110, 0, 0.1) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.upgrade-card:hover:not(.locked)::before {
    opacity: 1;
}

.upgrade-card:hover:not(.locked) {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(139, 0, 255, 0.5),
                0 0 15px rgba(255, 110, 0, 0.4);
    border-color: rgba(255, 110, 0, 0.6);
}

.upgrade-card.locked {
    opacity: 0.5;
    background: linear-gradient(135deg, rgba(26, 11, 46, 0.5), rgba(45, 27, 78, 0.5));
    border-color: rgba(255, 110, 0, 0.2);
}

.upgrade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.upgrade-header h4 {
    font-size: 1.125rem;
    color: #ff6e00;
    margin: 0;
    font-weight: 600;
    text-shadow: 0 0 5px rgba(255, 110, 0, 0.5);
}

.upgrade-level {
    font-size: 0.75rem;
    color: #b19cd9;
    background: rgba(26, 11, 46, 0.8);
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    border: 1px solid rgba(139, 0, 255, 0.4);
    box-shadow: 0 0 10px rgba(139, 0, 255, 0.3);
    text-shadow: 0 0 3px rgba(177, 156, 217, 0.4);
}

.upgrade-desc {
    color: #b19cd9;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
    line-height: 1.5;
    text-shadow: 0 0 3px rgba(177, 156, 217, 0.3);
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
    color: #ff6e00;
    text-shadow: 0 0 5px rgba(255, 110, 0, 0.5);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.upgrade-cost::before {
    content: '🎃';
    font-size: 1.3rem;
    animation: pumpkinSpin 4s ease-in-out infinite;
}

@keyframes pumpkinSpin {
    0%, 100% {
        transform: rotate(0deg);
    }
    25% {
        transform: rotate(-10deg);
    }
    75% {
        transform: rotate(10deg);
    }
}

.btn-upgrade {
    background: linear-gradient(135deg, #ff6e00, #ff8c33);
    color: white;
    font-weight: 600;
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 0 15px rgba(255, 110, 0, 0.4);
    position: relative;
    overflow: hidden;
}

.btn-upgrade::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-upgrade:hover:not(:disabled)::before {
    width: 300px;
    height: 300px;
}

.btn-upgrade:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 0 25px rgba(255, 110, 0, 0.6), 0 0 15px rgba(139, 0, 255, 0.4);
    background: linear-gradient(135deg, #ff8c33, #ff6e00);
}

.btn-upgrade:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    background: linear-gradient(135deg, rgba(177, 156, 217, 0.4), rgba(177, 156, 217, 0.3));
    box-shadow: none;
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
  transition: all 0.3s ease;
  box-shadow: 0 0 20px rgba(255, 110, 0, 0.3);
  position: relative;
  overflow: hidden;
}

.btn-play {
  background: linear-gradient(135deg, #ff6e00, #ff8c33);
  color: white;
  flex: 1;
  min-width: 250px;
  border: 2px solid rgba(255, 110, 0, 0.5);
}

.btn-play:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(255, 110, 0, 0.6), 0 0 20px rgba(139, 0, 255, 0.4);
  background: linear-gradient(135deg, #ff8c33, #ff6e00);
}

.btn-map {
  background: linear-gradient(135deg, #8b00ff, #9f1fff);
  color: white;
  flex: 1;
  min-width: 250px;
  border: 2px solid rgba(139, 0, 255, 0.5);
}

.btn-map:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(139, 0, 255, 0.6), 0 0 20px rgba(255, 110, 0, 0.4);
  background: linear-gradient(135deg, #9f1fff, #8b00ff);
}

@media (max-width: 768px) {
    .nest-display {
        padding: 1rem;
    }

    .nws {
        flex-direction: column;
        align-items: center;
    }

    .nest-canvas {
        width: 100%;
        height: auto;
        max-width: 400px;
    }

    .ns {
        width: 100%;
        max-width: 400px;
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