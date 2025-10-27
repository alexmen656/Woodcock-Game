<template>
  <div id="app">
    <header class="app-header">
      <div class="header-content">
        <div @click='router.push("/")' class="logo">
          <span class="logo-icon"></span>
          <img width="7.5%" src="./assets/logo.png" alt="">
          <h1>Nest Builder</h1>
        </div>
        <nav class="nav">
          <router-link to="/" class="nav-link">Nest</router-link>
          <router-link to="/map" class="nav-link">Map</router-link>
          <router-link to="/leaderboard" class="nav-link">Leaderboard</router-link>
          <router-link to="/game" class="nav-link">Play</router-link>
        </nav>
        <div class="username-section">
          <div v-if="!editingUsername" class="username-display" @click="startEditUsername">
            <svg class="username-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span class="username-text">{{ username }}</span>
            <svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
            </svg>
          </div>
          <div v-else class="username-edit">
            <input 
              ref="usernameInput"
              v-model="newUsername" 
              @keyup.enter="saveUsername"
              @blur="saveUsername"
              class="username-input"
              maxlength="20"
              placeholder="Username"
            />
          </div>
        </div>
      </div>
    </header>
    <main class="app-main">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useGameStore } from './store/gameStore'

const router = useRouter()
const gameStore = useGameStore()
const username = computed(() => gameStore.username.value)
const editingUsername = ref(false)
const newUsername = ref('')
const usernameInput = ref(null)

function startEditUsername() {
  newUsername.value = username.value
  editingUsername.value = true
  nextTick(() => {
    usernameInput.value?.focus()
  })
}

async function saveUsername() {
  if (newUsername.value.trim() && newUsername.value !== username.value) {
    await gameStore.setUsername(newUsername.value.trim())
  }
  editingUsername.value = false
}
</script>

<style scoped>
.app-header {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0.5rem 2rem;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 2rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.logo-icon {
  font-size: 2rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.logo h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
  letter-spacing: -0.02em;
  cursor: pointer;
  white-space: nowrap;
}

.nav {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.nav-link {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9375rem;
  color: var(--text-secondary);
  text-decoration: none;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.nav-link:hover {
  color: var(--primary);
  background: var(--bg-main);
  border-color: var(--border);
}

.nav-link.router-link-active {
  color: white;
  background: var(--accent);
  border-color: var(--accent);
}

.username-section {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.username-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--bg-main);
  border: 1px solid var(--border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.username-display:hover {
  border-color: var(--accent);
  background: var(--bg-card);
}

.username-icon {
  width: 20px;
  height: 20px;
  color: var(--primary);
}

.username-text {
  font-weight: 600;
  color: var(--primary);
  font-size: 0.9375rem;
}

.edit-icon {
  width: 16px;
  height: 16px;
  color: var(--text-secondary);
  opacity: 0.6;
}

.username-edit {
  display: flex;
  align-items: center;
}

.username-input {
  padding: 0.5rem 1rem;
  border: 2px solid var(--accent);
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--primary);
  background: white;
  outline: none;
  min-width: 150px;
}

.username-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.app-main {
  min-height: calc(100vh - 80px);
  padding: 1.5rem 0.75rem;
}

@media (max-width: 768px) {
  .header-content {
    padding: 1rem;
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .logo {
    justify-content: center;
  }
  
  .logo h1 {
    font-size: 1.25rem;
  }

  .logo-icon {
    font-size: 1.75rem;
  }

  .nav {
    justify-content: center;
  }

  .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  .username-section {
    justify-content: center;
  }
  

  .username-input {
    width: 100%;
    max-width: 250px;
  }
  
  .app-main {
    padding: 1rem 0.5rem;
  }
}
</style>
