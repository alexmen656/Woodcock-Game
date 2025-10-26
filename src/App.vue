<template>
  <div id="app">
    <header>
      <h1>Game</h1>
    </header>
    <main>
      <HomeView />
    </main>
  </div>
</template>

<script setup>
import HomeView from './views/HomeView.vue'
</script>

<style scoped>
header {
  text-align: center;
  padding: 1rem;
  background: linear-gradient(135deg, #ff9a56, #ff6b35);
  color: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

header h1 {
  margin: 0;
  font-size: 2rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

main {
  display: flex;
  justify-content: center;
  padding: 2rem 1rem;
}

@media (max-width: 768px) {
  header h1 {
    font-size: 1.5rem;
  }
  main {
    padding: 1rem 0.5rem;
  }
}
</style>
