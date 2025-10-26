import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import GameView from '../views/GameView.vue'
import LogoGen from '../views/logo_generator.vue'

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomeView
    },
    {
        path: '/game',
        name: 'game',
        component: GameView
    },
    {
        path: '/logo_gen',
        name: 'logo',
        component: LogoGen
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router