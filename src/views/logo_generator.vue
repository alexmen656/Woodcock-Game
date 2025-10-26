<template>
  <canvas ref="nestCanvasRef" width="2000" height="2000" class="nest-canvas"></canvas>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const nestCanvasRef = ref(null)
let nestCtx = null

function setupCanvas() {
  const canvas = nestCanvasRef.value
  if (!canvas) return
  nestCtx = canvas.getContext('2d')
  nestCtx.scale(5, 5)
  return nestCtx
}

const centerX = 200
const centerY = 220

function drawWoodcock(x, y) {
  const ctx = setupCanvas()
  ctx.save()
  ctx.fillStyle = '#8B6F47'
  ctx.beginPath()
  ctx.ellipse(x, y, 25, 20, 0, 0, Math.PI * 2)
  ctx.fill()

  ctx.fillStyle = '#A0826D'
  ctx.beginPath()
  ctx.ellipse(x + 15, y, 12, 15, 0.3, 0, Math.PI * 2)
  ctx.fill()

  ctx.fillStyle = '#8B6F47'
  ctx.beginPath()
  ctx.arc(x - 15, y - 10, 15, 0, Math.PI * 2)
  ctx.fill()

  ctx.fillStyle = '#CD853F'
  ctx.beginPath()
  ctx.moveTo(x - 15, y - 10)
  ctx.lineTo(x - 35, y - 12)
  ctx.lineTo(x - 35, y - 8)
  ctx.closePath()
  ctx.fill()

  ctx.fillStyle = 'black'
  ctx.beginPath()
  ctx.arc(x - 12, y - 13, 2, 0, Math.PI * 2)
  ctx.fill()
  ctx.restore()
}

onMounted(() => {
  drawWoodcock(centerX - 30, centerY - 50)
})
</script>
