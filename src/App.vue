<template>
  <div id="app">
    <header class="app-header">
      <div class="header-content">
        <div class="logo">
          <span class="logo-icon"></span>
          <h1>Nest Builder</h1>
        </div>
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

.app-main {
  min-height: calc(100vh - 80px);
  padding: 2rem 1rem;
}

@media (max-width: 768px) {
  .header-content {
    padding: 1rem;
  }
  
  .logo h1 {
    font-size: 1.25rem;
  }

  .logo-icon {
    font-size: 1.75rem;
  }
  
  .app-main {
    padding: 1rem 0.5rem;
  }
}
</style>
