<template>
  <div id="app">
    <header class="app-header">
      <div class="header-content">
        <div class="logo">
          <span class="logo-icon"></span>
          <img width="3.5%" src="./assets/logo.png" alt="">
          <h1>Nest Builder</h1>
        </div>
        <nav class="nav">
          <router-link to="/" class="nav-link">Nest</router-link>
          <router-link to="/map" class="nav-link">Karte</router-link>
          <router-link to="/game" class="nav-link">Spielen</router-link>
        </nav>
      </div>
    </header>
    <main class="app-main">
      <router-view />
    </main>
  </div>
</template>

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
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
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
}

.nav {
  display: flex;
  gap: 0.5rem;
}

.nav-link {
  padding: 0.625rem 1.25rem;
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

.app-main {
  min-height: calc(100vh - 80px);
  padding: 2rem 1rem;
}

@media (max-width: 768px) {
  .header-content {
    padding: 1rem;
    flex-direction: column;
    gap: 1rem;
  }
  
  .logo h1 {
    font-size: 1.25rem;
  }

  .logo-icon {
    font-size: 1.75rem;
  }

  .nav {
    width: 100%;
    justify-content: center;
  }

  .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
  
  .app-main {
    padding: 1rem 0.5rem;
  }
}
</style>
