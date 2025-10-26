import { ref } from 'vue'
import { LeaderboardAPI } from '../services/api'

const totalPoints = ref(0)
const nestLevel = ref(0)
const eggs = ref(0)
const decorations = ref(0)
const highscore = ref(0)
const username = ref('Guest_' + Math.floor(Math.random() * 10000))

function loadState() {
  try {
    const saved = localStorage.getItem('woodcock_game_state')
    if (saved) {
      const state = JSON.parse(saved)
      totalPoints.value = state.totalPoints || 0
      nestLevel.value = state.nestLevel || 0
      eggs.value = state.eggs || 0
      decorations.value = state.decorations || 0
      highscore.value = state.highscore || 0
      username.value = state.username || username.value
    }
  } catch (e) {
    console.warn('Failed to load state:', e)
  }
}

function saveState() {
  try {
    const state = {
      totalPoints: totalPoints.value,
      nestLevel: nestLevel.value,
      eggs: eggs.value,
      decorations: decorations.value,
      highscore: highscore.value,
      username: username.value
    }
    localStorage.setItem('woodcock_game_state', JSON.stringify(state))
  } catch (e) {
    console.warn('Failed to save state:', e)
  }
}

async function syncToBackend() {
  try {
    await LeaderboardAPI.updateLeaderboard({
      username: username.value,
      totalPoints: totalPoints.value,
      nestLevel: nestLevel.value,
      eggs: eggs.value,
      decorations: decorations.value,
      highscore: highscore.value
    })
    console.log('Synced to backend successfully')
  } catch (e) {
    console.warn('Failed to sync to backend:', e)
  }
}

loadState()

export function useGameStore() {
  return {
    totalPoints,
    nestLevel,
    eggs,
    decorations,
    highscore,
    username,
    
    setUsername(newUsername) {
      username.value = newUsername
      saveState()
    },
    
    async addPoints(points) {
      totalPoints.value += points
      saveState()
      await syncToBackend()
    },
    
    spendPoints(points) {
      if (totalPoints.value >= points) {
        totalPoints.value -= points
        saveState()
        syncToBackend()
        return true
      }
      return false
    },
    
    async upgradeNest() {
      if (nestLevel.value < 10) {
        nestLevel.value++
        saveState()
        await syncToBackend()
      }
    },
    
    async addEgg() {
      if (eggs.value < 5) {
        eggs.value++
        saveState()
        await syncToBackend()
      }
    },
    
    async addDecoration() {
      if (decorations.value < 8) {
        decorations.value++
        saveState()
        await syncToBackend()
      }
    },

    async updateHighscore(score) {
      if (score > highscore.value) {
        highscore.value = score
        saveState()
        await syncToBackend()
        return true
      }
      return false
    },

    async saveGameSession(score, leavesCollected, duration) {
      try {
        await LeaderboardAPI.saveGameSession({
          username: username.value,
          score,
          leavesCollected,
          duration
        })
      } catch (e) {
        console.warn('Failed to save game session:', e)
      }
    },
    
    reset() {
      totalPoints.value = 0
      nestLevel.value = 0
      eggs.value = 0
      decorations.value = 0
      highscore.value = 0
      saveState()
      syncToBackend()
    }
  }
}

