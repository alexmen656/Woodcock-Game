import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import GameView from '../views/GameView.vue'
import MapView from '../views/MapView.vue'
import Leaderboard from '../views/Leaderboard.vue'
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
        path: '/map',
        name: 'map',
        component: MapView
    },
    {
        path: '/leaderboard',
        name: 'leaderboard',
        component: Leaderboard
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