import { ref } from 'vue'
import { LeaderboardAPI } from '../services/api'

const totalPoints = ref(0)
const nestLevel = ref(0)
const eggs = ref(0)
const decorations = ref(0)
const highscore = ref(0)
const gamesPlayed = ref(0)
const userId = ref(null)

function getOrCreateUsername() {
  try {
    const saved = localStorage.getItem('woodcock_game_state')
    if (saved) {
      const state = JSON.parse(saved)
      if (state.username) {
        return state.username
      }
    }
  } catch (e) {
    console.warn('Failed to load username:', e)
  }

  return 'Guest_' + Math.floor(Math.random() * 10000)
}

const username = ref(getOrCreateUsername())

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
      gamesPlayed.value = state.gamesPlayed || 0
      userId.value = state.userId || null
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
      gamesPlayed: gamesPlayed.value,
      username: username.value,
      userId: userId.value
    }
    localStorage.setItem('woodcock_game_state', JSON.stringify(state))
  } catch (e) {
    console.warn('Failed to save state:', e)
  }
}

async function syncToBackend() {
  try {
    const response = await LeaderboardAPI.updateLeaderboard({
      username: username.value,
      totalPoints: totalPoints.value,
      nestLevel: nestLevel.value,
      eggs: eggs.value,
      decorations: decorations.value,
      highscore: highscore.value,
      gamesPlayed: gamesPlayed.value
    })
    
    if (response.success && response.data && response.data.user_id) {
      userId.value = response.data.user_id
      saveState()
    }
    
    console.log('Synced to backend successfully')
  } catch (e) {
    console.warn('Failed to sync to backend:', e)
  }
}

async function initializeUser() {
  if (userId.value) {
    console.log('User already initialized, skipping')
    return
  }
  
  try {
    const response = await LeaderboardAPI.updateLeaderboard({
      username: username.value,
      totalPoints: totalPoints.value,
      nestLevel: nestLevel.value,
      eggs: eggs.value,
      decorations: decorations.value,
      highscore: highscore.value,
      gamesPlayed: gamesPlayed.value
    })
    
    if (response.success && response.data && response.data.user_id) {
      userId.value = response.data.user_id
      saveState()
    }
    
    console.log('User initialized in backend')
  } catch (e) {
    console.warn('Failed to initialize user:', e)
  }
}

loadState()
initializeUser()

export function useGameStore() {
  return {
    totalPoints,
    nestLevel,
    eggs,
    decorations,
    highscore,
    gamesPlayed,
    username,
    
    async setUsername(newUsername) {
      const oldUsername = username.value
      username.value = newUsername
      saveState()
      
      try {
        await LeaderboardAPI.renameUser(userId.value, oldUsername, newUsername)
        console.log('Username updated successfully')
      } catch (e) {
        console.warn('Failed to update username:', e)
        await syncToBackend()
      }
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
        gamesPlayed.value++
        saveState()
        
        await LeaderboardAPI.saveGameSession({
          username: username.value,
          score,
          leavesCollected,
          duration
        })
        
        await syncToBackend()
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
    }
  }
}