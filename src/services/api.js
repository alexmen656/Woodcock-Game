import axios from 'axios'

const API_BASE_URL = 'https://alex.polan.sk/woodcock-game/api/leaderboard.php'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  }
})

function createFormData(data) {
  const formData = new FormData()
  for (const key in data) {
    formData.append(key, data[key])
  }
  return formData
}

export const LeaderboardAPI = {
  async getLeaderboard(limit = 50, offset = 0) {
    try {
      const response = await apiClient.get('', {
        params: {
          action: 'leaderboard',
          limit,
          offset
        }
      })
      return response.data
    } catch (error) {
      console.error('Failed to fetch leaderboard:', error)
      throw error
    }
  },

  async getPlayer(username) {
    try {
      const response = await apiClient.get('', {
        params: {
          action: 'player',
          username
        }
      })
      return response.data
    } catch (error) {
      console.error('Failed to fetch player:', error)
      throw error
    }
  },

  async updateLeaderboard(playerData) {
    try {
      const formData = createFormData({
        action: 'update_leaderboard',
        username: playerData.username,
        total_points: playerData.totalPoints,
        nest_level: playerData.nestLevel,
        eggs: playerData.eggs,
        decorations: playerData.decorations,
        highscore: playerData.highscore || 0,
        games_played: playerData.gamesPlayed || 0
      })

      const response = await apiClient.post('', formData)
      return response.data
    } catch (error) {
      console.error('Failed to update leaderboard:', error)
      throw error
    }
  },

  async saveGameSession(sessionData) {
    try {
      const formData = createFormData({
        action: 'save_game',
        username: sessionData.username,
        score: sessionData.score,
        leaves_collected: sessionData.leavesCollected || 0,
        duration: sessionData.duration || 0
      })

      const response = await apiClient.post('', formData)
      return response.data
    } catch (error) {
      console.error('Failed to save game session:', error)
      throw error
    }
  },

  async renameUser(userId, oldUsername, newUsername) {
    try {
      const formData = createFormData({
        action: 'rename_user',
        user_id: userId,
        old_username: oldUsername,
        new_username: newUsername
      })

      const response = await apiClient.post('', formData)
      return response.data
    } catch (error) {
      console.error('Failed to rename user:', error)
      throw error
    }
  },

  async getMapPlayers(limit = 20) {
    try {
      const response = await apiClient.get('', {
        params: {
          action: 'map_players',
          limit
        }
      })
      return response.data
    } catch (error) {
      console.error('Failed to fetch map players:', error)
      throw error
    }
  }
}

export default LeaderboardAPI
