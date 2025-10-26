<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
  setup() {
    const leaderboard = ref([])

    const fetchLeaderboard = async () => {
      try {
        const response = await axios.get('https://alex.polan.sk/woodcock-game/api/leaderboard.php?action=leaderboard')
        leaderboard.value = response.data
      } catch (error) {
        console.error('Error fetching leaderboard:', error)
      }
    }

    onMounted(() => {
      fetchLeaderboard()
    })

    return {
      leaderboard
    }
  }
}
</script>
<template>
  <div class="leaderboard">
    <h2>🏆 Bestenliste</h2>
    <table class="leaderboard-table">
      <thead>
        <tr>
          <th>Rang</th>
          <th>Spieler</th>
          <th>Punkte</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(entry, index) in leaderboard" :key="entry.id">
          <td>{{ index + 1 }}</td>
          <td>{{ entry.username }}</td>
          <td>{{ entry.score }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>