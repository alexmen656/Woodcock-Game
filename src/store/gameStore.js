import { ref } from 'vue'

const totalPoints = ref(0)
const nestLevel = ref(0)
const eggs = ref(0)
const decorations = ref(0)

function loadState() {
  try {
    const saved = localStorage.getItem('woodcock_game_state')
    if (saved) {
      const state = JSON.parse(saved)
      totalPoints.value = state.totalPoints || 0
      nestLevel.value = state.nestLevel || 0
      eggs.value = state.eggs || 0
      decorations.value = state.decorations || 0
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
      decorations: decorations.value
    }
    localStorage.setItem('woodcock_game_state', JSON.stringify(state))
  } catch (e) {
    console.warn('Failed to save state:', e)
  }
}

loadState()

export function useGameStore() {
  return {
    totalPoints,
    nestLevel,
    eggs,
    decorations,
    
    addPoints(points) {
      totalPoints.value += points
      saveState()
    },
    
    spendPoints(points) {
      if (totalPoints.value >= points) {
        totalPoints.value -= points
        saveState()
        return true
      }
      return false
    },
    
    upgradeNest() {
      if (nestLevel.value < 10) {
        nestLevel.value++
        saveState()
      }
    },
    
    addEgg() {
      if (eggs.value < 5) {
        eggs.value++
        saveState()
      }
    },
    
    addDecoration() {
      if (decorations.value < 8) {
        decorations.value++
        saveState()
      }
    },
    
    reset() {
      totalPoints.value = 0
      nestLevel.value = 0
      eggs.value = 0
      decorations.value = 0
      saveState()
    }
  }
}
