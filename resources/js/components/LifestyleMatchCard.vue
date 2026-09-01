<template>
  <div class="lmc" :class="canCompare ? 'is-member' : 'is-guest'">
    <div class="lmc-stack">
      <div class="lmc-radar-bg" :class="{ 'is-faded': !canCompare }" aria-hidden="true">
        <svg class="lmc-radar-svg" viewBox="0 0 280 280" role="img" aria-label="Lifestyle match radar chart">
          <polygon
            v-for="(ring, index) in rings"
            :key="'ring-' + index"
            :points="ring"
            class="lmc-ring"
            :class="{ 'is-mid': index === 2 }"
          />
          <line
            v-for="(spoke, index) in spokes"
            :key="'spoke-' + index"
            :x1="spoke.x1" :y1="spoke.y1" :x2="spoke.x2" :y2="spoke.y2"
            class="lmc-spoke"
          />
          <polygon :points="spaceShape.points" class="lmc-fill is-space" />
          <polygon
            v-if="canCompare"
            :points="youShape.points"
            class="lmc-fill is-you"
          />
          <polygon
            v-else
            :points="demoYouShape.points"
            class="lmc-fill is-you is-demo"
          />
          <circle
            v-for="(dot, index) in spaceShape.dots"
            :key="'s-' + index"
            :cx="dot.x" :cy="dot.y" r="4.5" class="lmc-dot is-space"
          />
          <circle
            v-if="canCompare"
            v-for="(dot, index) in youShape.dots"
            :key="'y-' + index"
            :cx="dot.x" :cy="dot.y" r="4.5" class="lmc-dot is-you"
          />
        </svg>
      </div>

      <div class="lmc-stack-front">
        <h2 class="lmc-title">Lifestyle Match</h2>

        <div class="lmc-stack-foot">
          <template v-if="canCompare">
            <div class="lmc-member-row">
              <div class="lmc-legend-col">
                <span class="lmc-legend-item is-space">Space<i></i></span>
                <span class="lmc-legend-item is-you">You<i></i></span>
              </div>
              <div class="lmc-score">{{ score }}%</div>
              <button type="button" class="lmc-toggle" @click="expanded = !expanded">
                Details
                <span class="lmc-toggle-arrow" :class="{ 'is-open': expanded }">&#9662;</span>
              </button>
            </div>
          </template>

          <template v-else>
            <div class="lmc-legend">
              <span class="lmc-legend-item is-space"><i></i>Space</span>
              <span class="lmc-legend-item is-you"><i></i>You?</span>
            </div>
            <div class="lmc-guest-text">
              <router-link to="/login"><b>Log in</b></router-link> to get your score
            </div>
          </template>
        </div>
      </div>
    </div>

    <div v-if="canCompare && expanded" class="lmc-axes">
      <div v-for="axis in axes" :key="axis.key" class="lmc-axis-row">
        <span>{{ axis.left }}</span>
        <span class="lmc-axis-track">
          <i class="lmc-axis-dot is-space" :style="{ left: fitPercent(space, axis.key) }"></i>
          <i class="lmc-axis-dot is-you" :style="{ left: fitPercent(myFits, axis.key) }"></i>
        </span>
        <span>{{ axis.right }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  space: { type: Object, default: null },
});

const axes = [
  { key: 'orgSpont', left: 'Organized', right: 'Spontaneous' },
  { key: 'quietLoud', left: 'Quiet', right: 'Loud' },
  { key: 'earlyNight', left: 'Early bird', right: 'Night owl' },
  { key: 'workFun', left: 'Work', right: 'Fun' },
  { key: 'expTrad', left: 'Traditional', right: 'Experimental' },
  { key: 'multiLocal', left: 'Local', right: 'Global' },
];

const RADAR = { cx: 140, cy: 140, r: 118, n: axes.length };

const expanded = ref(false);
const userLoggedIn = !!window.userLoggedIn;
const myFits = window.mate || null;

const canCompare = computed(() => userLoggedIn && !!myFits && !!props.space);

function clampFit(value) {
  const n = Number(value);
  return Number.isFinite(n) ? Math.min(10, Math.max(0, n)) : 5;
}

function fitPercent(source, key) {
  return (clampFit(source?.[key]) / 10) * 100 + '%';
}

function polar(index, ratio) {
  const angle = (index / RADAR.n) * Math.PI * 2 - Math.PI / 2;
  return {
    x: RADAR.cx + Math.cos(angle) * RADAR.r * ratio,
    y: RADAR.cy + Math.sin(angle) * RADAR.r * ratio,
  };
}

function shape(source) {
  if (!source) return { points: '', dots: [] };
  const dots = axes.map((axis, index) => polar(index, clampFit(source[axis.key]) / 10));
  return {
    points: dots.map(dot => `${dot.x.toFixed(1)},${dot.y.toFixed(1)}`).join(' '),
    dots,
  };
}

const rings = [0.2, 0.4, 0.6, 0.8, 1].map(ratio => (
  axes.map((_, index) => {
    const p = polar(index, ratio);
    return `${p.x.toFixed(1)},${p.y.toFixed(1)}`;
  }).join(' ')
));

const spokes = axes.map((_, index) => {
  const end = polar(index, 1);
  return { x1: RADAR.cx, y1: RADAR.cy, x2: end.x, y2: end.y };
});

const spaceShape = computed(() => shape(props.space));
const youShape = computed(() => shape(myFits));

const demoYouShape = computed(() => shape({
  orgSpont: clampFit(props.space?.orgSpont) + 2,
  quietLoud: clampFit(props.space?.quietLoud) - 3,
  earlyNight: clampFit(props.space?.earlyNight) + 1,
  workFun: clampFit(props.space?.workFun) - 2,
  expTrad: clampFit(props.space?.expTrad) + 1,
  multiLocal: clampFit(props.space?.multiLocal) - 1,
}));

const score = computed(() => {
  if (!canCompare.value) return null;
  const total = axes.reduce((sum, axis) => (
    sum + (10 - Math.abs(clampFit(props.space[axis.key]) - clampFit(myFits[axis.key]))) * 10
  ), 0);
  return Math.round(total / axes.length);
});
</script>

<style>
.lmc {
  border-radius: 5px;
  width: 100%;
  padding: 25px 10px 5px;
  background-color: rgb(246, 246, 246);
  border: 1px solid rgb(204, 204, 204);
  text-align: center;
}

.lmc.is-guest {
  padding-bottom: 25px;
}

.lmc.is-member {
  padding-bottom: 10px;
}

.lmc-title {
  margin: 0;
  font-size: 18px;
}

.lmc-stack {
  position: relative;
  min-height: 268px;
}

.lmc-radar-bg {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  transform: translateY(-15px);
}

.lmc.is-member .lmc-radar-bg {
  transform: translateY(-5px);
}

.lmc-radar-bg.is-faded {
  opacity: 0.42;
}

.lmc-radar-bg .lmc-radar-svg {
  width: 240px;
  max-width: 100%;
}

.lmc-radar-svg {
  width: 100%;
  height: auto;
  display: block;
}

.lmc-stack-front {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 268px;
}

.lmc-stack-foot {
  margin-top: auto;
  padding-top: 50px;
}

.lmc.is-member .lmc-stack,
.lmc.is-member .lmc-stack-front {
  min-height: 270px;
}

.lmc.is-member .lmc-stack-foot {
  padding-top: 38px;
}

.lmc-score {
  font-size: 28px;
  font-weight: 800;
  color: #0086da;
  line-height: 1;
  margin: 0 0 6px;
}

.lmc.is-member .lmc-score {
  color: #444;
  margin: 0;
}

.lmc-member-row {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
}

.lmc.is-member .lmc-member-row {
  padding: 0 5px 5px;
}

.lmc-legend-col {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: center;
  gap: 0;
  justify-self: start;
  width: 100%;
}

.lmc-legend-col .lmc-legend-item {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  font-size: 11px;
  line-height: 1.2;
  gap: 5px;
  padding-right: 30px;
}

.lmc-member-row .lmc-score {
  text-align: center;
  font-size: 26px;
  line-height: 1;
}

.lmc-member-row .lmc-toggle {
  margin-top: 0;
  justify-self: end;
}

.lmc-ring {
  fill: none;
  stroke: #e6eef4;
  stroke-width: 1;
}

.lmc-ring.is-mid {
  stroke: #c5d8e8;
  stroke-dasharray: 4 4;
}

.lmc-spoke {
  stroke: #d7e4ee;
  stroke-width: 1;
}

.lmc-fill {
  fill-opacity: 0.22;
  stroke-width: 2;
  stroke-linejoin: round;
}

.lmc-fill.is-space {
  fill: #0086da;
  stroke: #0086da;
}

.lmc-fill.is-you {
  fill: #e8833a;
  stroke: #e8833a;
}

.lmc-fill.is-demo {
  fill-opacity: 0.12;
  stroke-dasharray: 6 4;
}

.lmc-dot {
  stroke: #fff;
  stroke-width: 1.5;
}

.lmc-dot.is-space,
.lmc-legend-item.is-space i,
.lmc-axis-dot.is-space {
  fill: #0086da;
  background: #0086da;
}

.lmc-dot.is-you,
.lmc-legend-item.is-you i,
.lmc-axis-dot.is-you {
  fill: #e8833a;
  background: #e8833a;
}

.lmc-legend {
  display: flex;
  justify-content: center;
  gap: 14px;
}

.lmc-legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #555;
}

.lmc-legend-col .lmc-legend-item i {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.lmc-legend-item i {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.lmc-toggle {
  appearance: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: 1px solid #d4d4d4;
  background: #e8e8e8;
  border-radius: 4px;
  padding: 4px 10px;
  margin-top: 12px;
  font-size: 12px;
  font-weight: 500;
  color: #555;
  cursor: pointer;
  line-height: 1.2;
}

.lmc-toggle-arrow {
  display: inline-block;
  transition: transform 0.15s ease;
}

.lmc-toggle-arrow.is-open {
  transform: rotate(180deg);
}

.lmc-guest-text {
  margin-top: 23px;
}

.lmc-axes {
  display: grid;
  gap: 12px;
  margin-top: 12px;
  text-align: left;
}

.lmc-axis-row {
  display: grid;
  grid-template-columns: 76px 1fr 76px;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #555;
}

.lmc-axis-row span:first-child {
  text-align: right;
}

.lmc-axis-track {
  height: 6px;
  border-radius: 999px;
  background: #eef2f6;
  position: relative;
}

.lmc-axis-dot {
  position: absolute;
  top: -3px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  transform: translateX(-50%);
  box-shadow: 0 0 0 2px #fff;
}
</style>
