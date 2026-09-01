<template>
  <div
    class="space-photos"
    :class="{ 'is-dragover': isFileDragOver }"
    @dragenter.prevent="onFileDragEnter"
    @dragover.prevent="onFileDragOver"
    @dragleave.prevent="onFileDragLeave"
    @drop.prevent="onFileDrop"
  >
    <div class="space-photos-toolbar">
      <button
        type="button"
        class="space-photos-upload"
        :disabled="atLimit"
        @click="openAddPicker"
      >
        <span class="space-photos-upload-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7.5h3.1l1.2-2.1h7.4L16.9 7.5H20a2 2 0 0 1 2 2V18a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9.5a2 2 0 0 1 2-2Z" />
            <circle cx="12" cy="13.2" r="3.4" />
          </svg>
        </span>
        Upload photos (up to {{ maxPhotos }})
      </button>
      <p v-if="photos.length" class="space-photos-count">{{ photos.length }} / {{ maxPhotos }}</p>
    </div>

    <input
      ref="addInput"
      type="file"
      class="space-photos-file"
      accept="image/jpeg,image/png,image/webp"
      multiple
      @change="onAddSelected"
    >
    <input
      ref="replaceInput"
      type="file"
      class="space-photos-file"
      accept="image/jpeg,image/png,image/webp"
      @change="onReplaceSelected"
    >

    <p v-if="error" class="space-photos-error">{{ error }}</p>

    <div v-if="photos.length" class="space-photos-hint">
      <span class="space-photos-hint-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M8 11V7a2 2 0 1 1 4 0v8" />
          <path d="M12 9V7a2 2 0 1 1 4 0v6" />
          <path d="M16 11V8a2 2 0 1 1 4 0v8a5 5 0 0 1-5 5H9.5A5.5 5.5 0 0 1 4 15.5V13" />
        </svg>
      </span>
      Drag a photo to change its place. The first photo is the cover.
    </div>

    <div
      v-if="!photos.length"
      class="space-photos-empty"
      @click="openAddPicker"
    >
      Photos stay in their original shape. In this grid they appear as matching rectangles so the gallery is easy to scan.
      <strong>The first photo becomes the cover.</strong>
    </div>

    <draggable
      v-model="photos"
      item-key="key"
      class="space-photos-grid"
      ghost-class="space-photos-ghost"
      drag-class="space-photos-dragging"
      filter=".space-photos-action"
      :prevent-on-filter="true"
      animation="180"
    >
      <template #item="{ element, index }">
        <div class="space-photos-item">
          <div class="space-photos-slot-label">
            <span v-if="index === 0">Cover</span>
          </div>
          <div class="space-photos-tile">
            <img :src="element.url" alt="" draggable="false">
            <div class="space-photos-actions">
              <button
                type="button"
                class="space-photos-action"
                title="Replace this photo"
                aria-label="Replace this photo"
                @click.stop="startReplace(element.key)"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 16V4" />
                  <path d="m8 8 4-4 4 4" />
                  <path d="M5 16v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2" />
                </svg>
              </button>
              <button
                type="button"
                class="space-photos-action is-danger"
                title="Remove photo"
                aria-label="Remove photo"
                @click.stop="removePhoto(element.key)"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M7 7l10 10M17 7 7 17" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </template>
    </draggable>
  </div>
</template>

<script>
import draggable from 'vuedraggable';

const MAX_PHOTOS = 20;
const MAX_BYTES = 5 * 1024 * 1024;
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

function makePhotoKey() {
  return 'p_' + Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
}

export default {
  name: 'SpacePhotoManager',
  components: { draggable },
  props: {
    modelValue: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      error: '',
      replaceKey: '',
      isFileDragOver: false,
      fileDragDepth: 0,
    };
  },
  computed: {
    maxPhotos() {
      return MAX_PHOTOS;
    },
    photos: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      },
    },
    atLimit() {
      return this.photos.length >= MAX_PHOTOS;
    },
  },
  beforeUnmount() {
    this.photos.forEach((photo) => this.revokePreview(photo));
  },
  methods: {
    openAddPicker() {
      if (this.atLimit) return;
      this.error = '';
      this.$refs.addInput.value = '';
      this.$refs.addInput.click();
    },

    startReplace(key) {
      this.error = '';
      this.replaceKey = key;
      this.$refs.replaceInput.value = '';
      this.$refs.replaceInput.click();
    },

    onAddSelected(event) {
      this.addFiles(event.target.files);
      event.target.value = '';
    },

    onReplaceSelected(event) {
      const file = event.target.files && event.target.files[0];
      event.target.value = '';
      if (file) {
        this.replaceWithFile(this.replaceKey, file);
      }
      this.replaceKey = '';
    },

    onFileDragEnter(event) {
      if (!this.isFileDrag(event)) return;
      this.fileDragDepth += 1;
      this.isFileDragOver = true;
    },

    onFileDragOver(event) {
      if (!this.isFileDrag(event)) return;
      event.dataTransfer.dropEffect = this.atLimit ? 'none' : 'copy';
    },

    onFileDragLeave() {
      this.fileDragDepth = Math.max(0, this.fileDragDepth - 1);
      if (this.fileDragDepth === 0) {
        this.isFileDragOver = false;
      }
    },

    onFileDrop(event) {
      this.fileDragDepth = 0;
      this.isFileDragOver = false;
      if (!this.isFileDrag(event)) return;
      this.addFiles(event.dataTransfer.files);
    },

    isFileDrag(event) {
      return Array.from(event.dataTransfer?.types || []).includes('Files');
    },

    addFiles(fileList) {
      const incoming = Array.from(fileList || []);
      if (!incoming.length) return;

      const remaining = MAX_PHOTOS - this.photos.length;
      if (remaining <= 0) {
        this.error = `You can add up to ${MAX_PHOTOS} photos.`;
        return;
      }

      const accepted = [];
      for (const file of incoming.slice(0, remaining)) {
        const problem = this.validateFile(file);
        if (problem) {
          this.error = problem;
          return;
        }
        accepted.push(this.createPhoto(file));
      }

      if (incoming.length > remaining) {
        this.error = `Only ${remaining} more photo${remaining === 1 ? '' : 's'} can be added.`;
      } else {
        this.error = '';
      }

      this.photos = [...this.photos, ...accepted];
    },

    replaceWithFile(key, file) {
      const problem = this.validateFile(file);
      if (problem) {
        this.error = problem;
        return;
      }

      this.photos = this.photos.map((photo) => {
        if (photo.key !== key) return photo;
        this.revokePreview(photo);
        return {
          ...photo,
          url: URL.createObjectURL(file),
          file,
        };
      });
      this.error = '';
    },

    removePhoto(key) {
      const next = [];
      this.photos.forEach((photo) => {
        if (photo.key === key) {
          this.revokePreview(photo);
          return;
        }
        next.push(photo);
      });
      this.photos = next;
      this.error = '';
    },

    createPhoto(file) {
      return {
        key: makePhotoKey(),
        id: null,
        url: URL.createObjectURL(file),
        file,
      };
    },

    validateFile(file) {
      if (!ALLOWED_TYPES.includes(file.type)) {
        return `${file.name} must be a JPEG, PNG, or WebP image.`;
      }
      if (file.size > MAX_BYTES) {
        return `${file.name} is larger than 5MB.`;
      }
      return '';
    },

    revokePreview(photo) {
      if (photo?.url && photo.url.startsWith('blob:')) {
        URL.revokeObjectURL(photo.url);
      }
    },
  },
};
</script>

<style scoped>
.space-photos {
  position: relative;
}

.space-photos.is-dragover {
  outline: 2px dashed var(--success-border);
  outline-offset: 8px;
  border-radius: 12px;
}

.space-photos-toolbar {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.space-photos-upload {
  appearance: none;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  min-height: 44px;
  padding: 0 16px;
  border: 1px solid var(--success-border);
  border-radius: 10px;
  background: #fff;
  color: #1d6b2a;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.space-photos-upload:hover:not(:disabled) {
  background: #f3ffee;
}

.space-photos-upload:disabled {
  opacity: .55;
  cursor: default;
}

.space-photos-upload-icon {
  display: inline-grid;
  place-items: center;
  width: 22px;
  height: 22px;
  color: #3ca14c;
}

.space-photos-upload-icon svg {
  width: 22px;
  height: 22px;
}

.space-photos-count {
  margin: 0;
  color: #666;
  font-size: 13px;
  font-weight: 600;
}

.space-photos-file {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.space-photos-error {
  margin: 12px 0 0;
  color: #c0392b;
  font-size: 13px;
  font-weight: 600;
}

.space-photos-hint {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 14px;
  padding: 10px 12px;
  border-radius: 10px;
  background: #eef9e8;
  color: #2d5a34;
  font-size: 13px;
  font-weight: 600;
}

.space-photos-hint-icon {
  display: inline-grid;
  place-items: center;
  width: 22px;
  height: 22px;
  flex-shrink: 0;
}

.space-photos-hint-icon svg {
  width: 22px;
  height: 22px;
}

.space-photos-empty {
  margin-top: 14px;
  padding: 28px 18px;
  border: 1px dashed #cfd8cf;
  border-radius: 12px;
  background: #fff;
  color: #666;
  font-size: 14px;
  line-height: 1.5;
  text-align: center;
  cursor: pointer;
}

.space-photos-empty strong {
  display: block;
  margin-top: 6px;
  color: #333;
}

.space-photos-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-top: 14px;
}

.space-photos-item {
  min-width: 0;
}

.space-photos-slot-label {
  height: 18px;
  margin-bottom: 5px;
  color: #4a7a3a;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}

.space-photos-tile {
  position: relative;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  border-radius: 10px;
  background: #e8e8e8;
  cursor: grab;
}

.space-photos-tile img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
  pointer-events: none;
  user-select: none;
}

.space-photos-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 6px;
}

.space-photos-action {
  display: grid;
  place-items: center;
  width: 28px;
  height: 28px;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, .88);
  color: #333;
  box-shadow: 0 1px 4px rgba(0, 0, 0, .18);
  cursor: pointer;
}

.space-photos-action:hover {
  background: #fff;
}

.space-photos-action.is-danger {
  color: #b42318;
}

.space-photos-action svg {
  width: 14px;
  height: 14px;
}

.space-photos-ghost {
  opacity: .4;
}

.space-photos-dragging {
  cursor: grabbing;
}

@media screen and (max-width: 800px) {
  .space-photos-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media screen and (max-width: 560px) {
  .space-photos-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
