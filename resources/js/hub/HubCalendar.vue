<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  uid: { type: String, required: true },
  isAdmin: { type: Boolean, default: false },
  events: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['refresh']);

const WEEKDAYS = [
  { full: 'Mon', short: 'M' },
  { full: 'Tue', short: 'T' },
  { full: 'Wed', short: 'W' },
  { full: 'Thu', short: 'T' },
  { full: 'Fri', short: 'F' },
  { full: 'Sat', short: 'S' },
  { full: 'Sun', short: 'S' },
];

const todayKey = toDateKey(new Date());
const viewDate = ref(startOfMonth(new Date()));
const dragging = ref(false);
const rangeStart = ref(null);
const rangeEnd = ref(null);
const composerOpen = ref(false);
const selectedEvent = ref(null);
const actionError = ref('');
const actionLoading = ref(false);
const form = ref(blankForm('event'));

const monthLabel = computed(() => viewDate.value.toLocaleDateString('en-US', {
  month: 'long',
  year: 'numeric',
}));

const cells = computed(() => buildMonthCells(viewDate.value));

const selectedStart = computed(() => normalizeRange(rangeStart.value, rangeEnd.value).start);
const selectedEnd = computed(() => normalizeRange(rangeStart.value, rangeEnd.value).end);

const selectedLabel = computed(() => {
  if (!selectedStart.value) return '';
  return formatRangeLabel(selectedStart.value, selectedEnd.value);
});

const upcoming = computed(() => {
  return [...props.events]
    .filter(event => (event.end_date || event.start_date || '') >= todayKey)
    .sort((a, b) => (a.start_date || '').localeCompare(b.start_date || ''))
    .slice(0, 8);
});

function blankForm(kind) {
  return {
    kind: kind || 'event',
    title: '',
    start_time: '',
    end_time: '',
    location: '',
    description: '',
  };
}

function startOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1);
}

function toDateKey(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function parseDateKey(key) {
  const [year, month, day] = key.split('-').map(Number);
  return new Date(year, month - 1, day);
}

function addDays(date, amount) {
  const next = new Date(date);
  next.setDate(next.getDate() + amount);
  return next;
}

function buildMonthCells(monthDate) {
  const first = startOfMonth(monthDate);
  const mondayOffset = (first.getDay() + 6) % 7;
  const gridStart = addDays(first, -mondayOffset);
  const days = [];

  for (let index = 0; index < 42; index += 1) {
    const date = addDays(gridStart, index);
    days.push({
      key: toDateKey(date),
      day: date.getDate(),
      inMonth: date.getMonth() === monthDate.getMonth(),
    });
  }

  return days;
}

function normalizeRange(start, end) {
  if (!start) return { start: null, end: null };
  const finish = end || start;
  return start <= finish
    ? { start, end: finish }
    : { start: finish, end: start };
}

function formatRangeLabel(start, end) {
  const startDate = parseDateKey(start);
  const endDate = parseDateKey(end || start);
  const sameMonth = startDate.getMonth() === endDate.getMonth() && startDate.getFullYear() === endDate.getFullYear();
  const startText = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  if (start === end) return startText;
  const endText = endDate.toLocaleDateString('en-US', sameMonth ? { day: 'numeric' } : { month: 'short', day: 'numeric' });
  return `${startText} – ${endText}`;
}

function formatEventRange(event) {
  if (!event?.start_date) return '';
  return formatRangeLabel(event.start_date, event.end_date || event.start_date);
}

function isSelected(dayKey) {
  if (!selectedStart.value) return false;
  return dayKey >= selectedStart.value && dayKey <= selectedEnd.value;
}

function itemsForDay(dayKey) {
  return props.events.filter(event => (
    event.start_date
    && event.end_date
    && event.start_date <= dayKey
    && event.end_date >= dayKey
  ));
}

function isDayOff(dayKey) {
  return itemsForDay(dayKey).some(event => event.kind === 'day_off');
}

function visibleItems(dayKey) {
  return itemsForDay(dayKey).slice(0, 3);
}

function extraCount(dayKey) {
  return Math.max(0, itemsForDay(dayKey).length - 3);
}

function shiftMonth(delta) {
  viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() + delta, 1);
}

function onDayPointerDown(dayKey, event) {
  if (event.button !== undefined && event.button !== 0) return;
  event.preventDefault();
  selectedEvent.value = null;
  dragging.value = true;
  rangeStart.value = dayKey;
  rangeEnd.value = dayKey;
  composerOpen.value = false;
}

function onDayPointerEnter(dayKey) {
  if (!dragging.value) return;
  rangeEnd.value = dayKey;
}

function stopDragging() {
  if (!dragging.value) return;
  dragging.value = false;
  if (rangeStart.value) {
    composerOpen.value = true;
    form.value = blankForm(props.isAdmin ? form.value.kind || 'event' : 'event');
    actionError.value = '';
  }
}

function cancelComposer() {
  composerOpen.value = false;
  selectedEvent.value = null;
  rangeStart.value = null;
  rangeEnd.value = null;
  actionError.value = '';
  form.value = blankForm('event');
}

function openEvent(event) {
  dragging.value = false;
  selectedEvent.value = event;
  composerOpen.value = false;
  rangeStart.value = event.start_date;
  rangeEnd.value = event.end_date || event.start_date;
}

async function saveEntry() {
  if (!selectedStart.value) return;

  if (form.value.kind === 'event' && !form.value.title.trim()) {
    actionError.value = 'Add an event title.';
    return;
  }

  actionLoading.value = true;
  actionError.value = '';

  try {
    await axios.post(`/api/space/${props.uid}/hub/events`, {
      kind: form.value.kind,
      title: form.value.title,
      description: form.value.description,
      start_date: selectedStart.value,
      end_date: selectedEnd.value,
      start_time: form.value.start_time || null,
      end_time: form.value.end_time || null,
      location: form.value.location,
    });
    cancelComposer();
    emit('refresh');
  } catch (error) {
    actionError.value = error.response?.data?.message || 'Could not save this to the calendar.';
  } finally {
    actionLoading.value = false;
  }
}

async function deleteEvent(event) {
  if (!confirm(event.kind === 'day_off' ? 'Remove this day off?' : 'Remove this event?')) return;

  actionLoading.value = true;
  actionError.value = '';
  try {
    await axios.delete(`/api/space/${props.uid}/hub/events/${event.id}`);
    selectedEvent.value = null;
    emit('refresh');
  } catch (error) {
    actionError.value = error.response?.data?.message || 'Could not remove this.';
  } finally {
    actionLoading.value = false;
  }
}

function onKeydown(event) {
  if (event.key === 'Escape') cancelComposer();
}

watch(() => props.isAdmin, (isAdmin) => {
  if (!isAdmin && form.value.kind === 'day_off') {
    form.value.kind = 'event';
  }
});

onMounted(() => {
  window.addEventListener('pointerup', stopDragging);
  window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener('pointerup', stopDragging);
  window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
  <div class="cal">
    <div class="hub-section-header">
      <div>
        <h2 class="hub-section-title">Calendar</h2>
        <div class="hub-section-subtitle">
          Click a day or drag across dates.
          <template v-if="isAdmin"> Hosts can mark days off; anyone in the house can add events.</template>
          <template v-else> Add house events on any day. Days off are set by the host.</template>
        </div>
      </div>
      <div class="cal-nav">
        <button type="button" class="cal-nav-btn" @click="shiftMonth(-1)" aria-label="Previous month">‹</button>
        <div class="cal-month">{{ monthLabel }}</div>
        <button type="button" class="cal-nav-btn" @click="shiftMonth(1)" aria-label="Next month">›</button>
      </div>
    </div>

    <div class="cal-legend">
      <span><i class="cal-swatch cal-swatch-off"></i> Day off</span>
      <span><i class="cal-swatch cal-swatch-event"></i> Event</span>
      <span><i class="cal-swatch cal-swatch-selected"></i> Selected</span>
    </div>

    <div class="cal-board">
      <div class="cal-weekdays">
        <div v-for="weekday in WEEKDAYS" :key="weekday.full">
          <span class="cal-wd-full">{{ weekday.full }}</span>
          <span class="cal-wd-short">{{ weekday.short }}</span>
        </div>
      </div>
      <div class="cal-grid">
        <button
          v-for="cell in cells"
          :key="cell.key"
          type="button"
          class="cal-cell"
          :class="{
            muted: !cell.inMonth,
            today: cell.key === todayKey,
            off: isDayOff(cell.key),
            selected: isSelected(cell.key),
          }"
          @pointerdown="onDayPointerDown(cell.key, $event)"
          @pointerenter="onDayPointerEnter(cell.key)"
        >
          <div class="cal-cell-top">
            <span class="cal-daynum">{{ cell.day }}</span>
            <span v-if="isDayOff(cell.key)" class="cal-off-label">Off</span>
          </div>
          <div class="cal-chips">
            <span
              v-for="item in visibleItems(cell.key)"
              :key="item.id"
              class="cal-chip"
              :class="item.kind === 'day_off' ? 'is-off' : 'is-event'"
              @pointerdown.stop
              @click.stop="openEvent(item)"
            >{{ item.title }}</span>
            <span v-if="extraCount(cell.key)" class="cal-more">+{{ extraCount(cell.key) }}</span>
          </div>
        </button>
      </div>
    </div>

    <div v-if="composerOpen" class="hub-form-card cal-composer">
      <div class="cal-composer-kicker">{{ selectedLabel }}</div>
      <div v-if="isAdmin" class="cal-kind">
        <button type="button" class="hub-chip" :class="{ active: form.kind === 'event' }" @click="form.kind = 'event'">Event</button>
        <button type="button" class="hub-chip" :class="{ active: form.kind === 'day_off' }" @click="form.kind = 'day_off'">Day off</button>
      </div>

      <label for="cal-title">{{ form.kind === 'day_off' ? 'Label' : 'Event title' }}</label>
      <input
        id="cal-title"
        v-model="form.title"
        type="text"
        class="input"
        :placeholder="form.kind === 'day_off' ? 'Optional, e.g. Host away' : 'House dinner, cleaning day…'"
      >

      <template v-if="form.kind === 'event'">
        <div class="hub-two-col">
          <div>
            <label for="cal-start-time">Starts</label>
            <input id="cal-start-time" v-model="form.start_time" type="time" class="input">
          </div>
          <div>
            <label for="cal-end-time">Ends</label>
            <input id="cal-end-time" v-model="form.end_time" type="time" class="input">
          </div>
        </div>
        <label for="cal-location">Location</label>
        <input id="cal-location" v-model="form.location" type="text" class="input" placeholder="Optional">
      </template>

      <label for="cal-description">{{ form.kind === 'day_off' ? 'Note' : 'Description' }}</label>
      <textarea id="cal-description" v-model="form.description" class="textarea" placeholder="Optional" style="height:90px;"></textarea>

      <div class="hub-form-actions">
        <span v-if="actionError" class="hub-error">{{ actionError }}</span>
        <button type="button" class="button-function" @click="cancelComposer">Cancel</button>
        <button type="button" class="button" :disabled="actionLoading" @click="saveEntry">
          {{ actionLoading ? 'Saving...' : (form.kind === 'day_off' ? 'Mark days off' : 'Add event') }}
        </button>
      </div>
    </div>

    <div v-else-if="selectedEvent" class="hub-form-card cal-composer">
      <div class="cal-chip" :class="selectedEvent.kind === 'day_off' ? 'is-off' : 'is-event'" style="width:fit-content;">
        {{ selectedEvent.kind === 'day_off' ? 'Day off' : 'Event' }}
      </div>
      <div class="hub-event-title" style="margin-top:8px;">{{ selectedEvent.title }}</div>
      <div class="hub-event-date">{{ formatEventRange(selectedEvent) }}</div>
      <div v-if="selectedEvent.location" class="hub-member-meta">{{ selectedEvent.location }}</div>
      <div v-if="selectedEvent.description" class="hub-event-description">{{ selectedEvent.description }}</div>
      <div class="hub-form-actions">
        <span v-if="actionError" class="hub-error">{{ actionError }}</span>
        <button type="button" class="button-function" @click="cancelComposer">Close</button>
        <button
          v-if="isAdmin || selectedEvent.is_mine"
          type="button"
          class="button-function"
          :disabled="actionLoading"
          @click="deleteEvent(selectedEvent)"
        >Remove</button>
      </div>
    </div>

    <div v-if="loading" class="hub-empty" style="margin-top:18px;">Loading calendar...</div>
    <div v-else-if="upcoming.length" class="cal-upcoming">
      <h3>Upcoming</h3>
      <article v-for="event in upcoming" :key="event.id" class="hub-event-card" @click="openEvent(event)">
        <div class="hub-event-date">{{ formatEventRange(event) }} · {{ event.kind === 'day_off' ? 'Day off' : 'Event' }}</div>
        <div class="hub-event-title">{{ event.title }}</div>
        <div v-if="event.location" class="hub-member-meta">{{ event.location }}</div>
      </article>
    </div>
    <div v-else class="hub-empty" style="margin-top:18px;">No upcoming events or days off yet. Select dates on the grid to add one.</div>
  </div>
</template>

<style scoped>
.hub-section-header {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.hub-section-title {
  margin: 0 0 4px;
  padding-top: 0;
}

.hub-section-subtitle,
.hub-member-meta {
  color: rgb(110, 110, 110);
  font-size: 13px;
}

.hub-form-card,
.hub-event-card {
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
}

.hub-form-card {
  padding: 16px;
}

.hub-event-card {
  padding: 15px;
}

.hub-form-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
}

.hub-chip {
  border: 1px solid #d8d8d8;
  background: #fff;
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 13px;
  color: #444;
  cursor: pointer;
}

.hub-chip.active,
.hub-chip:hover {
  background: #00abff;
  border-color: #00abff;
  color: #fff;
}

.hub-two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.hub-empty {
  padding: 30px 10px;
  text-align: center;
  color: rgb(120, 120, 120);
  background: #fafafa;
  border: 1px dashed #ddd;
  border-radius: 10px;
}

.hub-error {
  color: #d0342c;
  font-size: 13px;
}

.hub-event-date {
  color: #00abff;
  font-size: 13px;
  font-weight: 700;
}

.hub-event-title {
  margin-top: 4px;
  font-size: 17px;
  font-weight: 700;
}

.hub-event-description {
  line-height: 1.6;
}

.cal-nav {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cal-month {
  min-width: 150px;
  text-align: center;
  font-size: 16px;
  font-weight: 700;
}

.cal-nav-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #d8d8d8;
  border-radius: 10px;
  background: #fff;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
}

.cal-nav-btn:hover {
  border-color: #00abff;
  color: #00abff;
}

.cal-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin: 0 0 14px;
  color: rgb(110, 110, 110);
  font-size: 13px;
}

.cal-legend span {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.cal-swatch {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.cal-swatch-off {
  background: repeating-linear-gradient(-45deg, #f0f0f0, #f0f0f0 4px, #ddd 4px, #ddd 8px);
}

.cal-swatch-event {
  background: #d7f0ff;
  border-color: #9ad6f5;
}

.cal-swatch-selected {
  background: #e8f6ff;
  border-color: #00abff;
}

.cal-board {
  user-select: none;
  min-width: 0;
  width: 100%;
}

.cal-weekdays,
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 4px;
}

.cal-weekdays {
  margin-bottom: 4px;
}

.cal-weekdays div {
  padding: 0 6px 6px;
  color: rgb(120, 120, 120);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  text-align: center;
}

.cal-wd-short {
  display: none;
}

.cal-cell {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  min-height: 108px;
  padding: 8px;
  border: 1px solid #e6e6e6;
  border-radius: 10px;
  background: #fff;
  text-align: left;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
  touch-action: none;
}

.cal-cell:hover:not(.selected) {
  border-color: #cfe9ff;
}

.cal-cell.muted {
  background: #fafafa;
  color: #bbb;
}

.cal-cell.off {
  background: repeating-linear-gradient(-45deg, #f7f7f7, #f7f7f7 6px, #efefef 6px, #efefef 12px);
}

.cal-cell.selected {
  background: #e8f6ff;
  border-color: #00abff;
  box-shadow: inset 0 0 0 1px #00abff;
}

.cal-cell.today .cal-daynum {
  background: #00abff;
  color: #fff;
}

.cal-cell-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.cal-daynum {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  font-size: 13px;
  font-weight: 700;
}

.cal-off-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: #888;
}

.cal-chips {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-height: 0;
}

.cal-chip {
  display: block;
  overflow: hidden;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  line-height: 1.4;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.cal-chip.is-event {
  background: #d7f0ff;
  color: #0a6ea0;
}

.cal-chip.is-off {
  background: #ececec;
  color: #555;
}

.cal-more {
  font-size: 11px;
  color: #888;
}

.cal-composer {
  margin-top: 16px;
}

.cal-composer .input,
.cal-composer .textarea {
  width: 100%;
}

.cal-composer-kicker {
  margin-bottom: 10px;
  color: #00abff;
  font-size: 13px;
  font-weight: 700;
}

.cal-kind {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.cal-upcoming {
  margin-top: 22px;
}

.cal-upcoming h3 {
  margin-top: 0;
}

.cal-upcoming .hub-event-card {
  cursor: pointer;
}

.cal-upcoming .hub-event-card + .hub-event-card {
  margin-top: 10px;
}

@media screen and (max-width: 700px) {
  .hub-section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .cal-nav {
    justify-content: space-between;
  }

  .cal-month {
    min-width: 0;
    flex: 1;
  }

  .hub-two-col {
    grid-template-columns: 1fr;
  }

  .hub-form-actions {
    justify-content: flex-start;
  }

  .hub-form-actions .button,
  .hub-form-actions .button-function {
    min-height: 40px;
  }

  .cal-legend {
    gap: 8px 12px;
    font-size: 12px;
  }

  .cal-wd-full {
    display: none;
  }

  .cal-wd-short {
    display: inline;
  }

  .cal-weekdays div {
    padding: 0 0 4px;
    font-size: 11px;
  }

  .cal-grid {
    gap: 3px;
  }

  .cal-cell {
    min-height: 52px;
    padding: 5px 3px;
    border-radius: 6px;
  }

  .cal-cell-top {
    margin-bottom: 2px;
  }

  .cal-daynum {
    width: 20px;
    height: 20px;
    font-size: 11px;
  }

  .cal-off-label {
    display: none;
  }

  .cal-chips {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 3px;
  }

  .cal-chip {
    width: 7px;
    height: 7px;
    padding: 0;
    border-radius: 50%;
    font-size: 0;
    line-height: 0;
    color: transparent;
  }

  .cal-more {
    display: none;
  }
}

@media screen and (max-width: 480px) {
  .cal-nav-btn {
    width: 34px;
    height: 34px;
  }

  .cal-month {
    font-size: 14px;
  }

  .cal-grid,
  .cal-weekdays {
    gap: 2px;
  }

  .cal-cell {
    min-height: 46px;
    padding: 4px 2px;
  }

  .cal-composer,
  .hub-event-card {
    padding: 12px;
  }
}
</style>
