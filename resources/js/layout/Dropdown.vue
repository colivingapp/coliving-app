<template>
    <div  >
        <div @click="toggleMenu()">
            <slot name="trigger"></slot>
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div v-show="open" class="fixed inset-0 z-40" @click="closeMenu()">
        </div>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-show="open"
                    class="absolute z-50 mt-2 rounded-md shadow-lg"
                    :class="[widthClass, alignmentClasses]"
                    @click="closeMenu()">
                <div class="rounded-md shadow-xs" style="text-align:left;" :class="contentClasses">
                    <slot name="content" ></slot>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        props: {
            align: {
                default: 'right'
            },
            width: {
                default: '48'
            },
            contentClasses: {
                default: () => ['py-1', 'bg-white']
            }
        },

        data() {
            return {
                open: false
            }
        },

        created() {
            const closeOnEscape = (e) => {
                if (this.open && e.keyCode === 27) {
                    this.open = false;
					this.closeMenu();
                }
            };

            const closeOnClickOutside = (e) => {
                // Check if the click was outside the menu
                if (this.open && !this.$el.contains(e.target)) {
                    this.open = false;
					this.closeMenu();
                }
            };

            // Add the keydown event listener when the component is created
            document.addEventListener('keydown', closeOnEscape);

            // Add the click event listener for outside clicks
            document.addEventListener('click', closeOnClickOutside);

            // Store the function references for cleanup
            this.closeOnEscape = closeOnEscape;
            this.closeOnClickOutside = closeOnClickOutside;
        },

        beforeUnmount() {
            // Cleanup the event listeners before the component is unmounted
            document.removeEventListener('keydown', this.closeOnEscape);
            document.removeEventListener('click', this.closeOnClickOutside);
        },
		methods: {
			toggleMenu() {
				this.open = !this.open;
				this.$emit('menu-toggle', this.open);
			},
			closeMenu() {
				this.open = false;
				this.$emit('menu-toggle', this.open);
			}
		},
        computed: {
            widthClass() {
                return {
                    '48': 'w-48',
                }[this.width.toString()]
            },

            alignmentClasses() {
                if (this.align == 'left') {
                    return 'origin-top-left left-0'
                } else if (this.align == 'right') {
                    return 'origin-top-right right-0'
                } else {
                    return 'origin-top'
                }
            },
        }
    }
</script>

<style>
.absolute {
    position: absolute;
}

.relative {
	position: relative
}

.inset-0 {
	top: 0;
	bottom: 0
}

.inset-0,
.inset-x-0 {
	right: 0;
	left: 0
}

.z-40 {
	z-index: 40
}

.transform {
	--transform-translate-x: 0;
	--transform-translate-y: 0;
	--transform-rotate: 0;
	--transform-skew-x: 0;
	--transform-skew-y: 0;
	--transform-scale-x: 1;
	--transform-scale-y: 1;
	transform: translateX(var(--transform-translate-x)) translateY(var(--transform-translate-y)) rotate(var(--transform-rotate)) skewX(var(--transform-skew-x)) skewY(var(--transform-skew-y)) scaleX(var(--transform-scale-x)) scaleY(var(--transform-scale-y))
}

.scale-95 {
	--transform-scale-x: .95;
	--transform-scale-y: .95
}

.scale-100 {
	--transform-scale-x: 1;
	--transform-scale-y: 1
}

.translate-y-0 {
	--transform-translate-y: 0
}

.translate-y-4 {
	--transform-translate-y: 1rem
}

.transition-all {
	transition-property: all
}

.transition {
	transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform
}

.ease-in {
	transition-timing-function: cubic-bezier(.4, 0, 1, 1)
}

.ease-out {
	transition-timing-function: cubic-bezier(0, 0, .2, 1)
}

.ease-in-out {
	transition-timing-function: cubic-bezier(.4, 0, .2, 1)
}

.duration-75 {
	transition-duration: 75ms
}

.duration-150 {
	transition-duration: .15s
}

.duration-200 {
	transition-duration: .2s
}

.duration-300 {
	transition-duration: .3s
}

.duration-1000 {
	transition-duration: 1s
}

@-webkit-keyframes spin {
	to {
		transform: rotate(1turn)
	}
}

@keyframes spin {
	to {
		transform: rotate(1turn)
	}
}

@-webkit-keyframes ping {
	75%,
	to {
		transform: scale(2);
		opacity: 0
	}
}

@keyframes ping {
	75%,
	to {
		transform: scale(2);
		opacity: 0
	}
}

@-webkit-keyframes pulse {
	50% {
		opacity: .5
	}
}

@keyframes pulse {
	50% {
		opacity: .5
	}
}

@-webkit-keyframes bounce {
	0%,
	to {
		transform: translateY(-25%);
		-webkit-animation-timing-function: cubic-bezier(.8, 0, 1, 1);
		animation-timing-function: cubic-bezier(.8, 0, 1, 1)
	}
	50% {
		transform: none;
		-webkit-animation-timing-function: cubic-bezier(0, 0, .2, 1);
		animation-timing-function: cubic-bezier(0, 0, .2, 1)
	}
}

@keyframes bounce {
	0%,
	to {
		transform: translateY(-25%);
		-webkit-animation-timing-function: cubic-bezier(.8, 0, 1, 1);
		animation-timing-function: cubic-bezier(.8, 0, 1, 1)
	}
	50% {
		transform: none;
		-webkit-animation-timing-function: cubic-bezier(0, 0, .2, 1);
		animation-timing-function: cubic-bezier(0, 0, .2, 1)
	}
}


.sm\:block{display:block}

@media (min-width:768px) {
	.md\:border-t-0 {
		border-top-width: 0
	}
	.md\:border-l {
		border-left-width: 1px
	}
	.md\:grid {
		display: grid
	}
	.md\:mt-0 {
		margin-top: 0
	}
	.md\:gap-6 {
		grid-gap: 1.5rem;
		gap: 1.5rem
	}
	.md\:grid-cols-2 {
		grid-template-columns: repeat(2, minmax(0, 1fr))
	}
	.md\:grid-cols-3 {
		grid-template-columns: repeat(3, minmax(0, 1fr))
	}
	.md\:col-span-1 {
		grid-column: span 1/span 1
	}
	.md\:col-span-2 {
		grid-column: span 2/span 2
	}
}

@media (min-width:1024px) {
	.lg\:px-8 {
		padding-left: 2rem;
		padding-right: 2rem
	}
}


.shadow-xs {
	box-shadow: 0 0 0 1px rgba(0, 0, 0, .05)
}

.py-1 {
	padding-top: .25rem;
	padding-bottom: .25rem
}

.bg-white {
	--bg-opacity: 1;
	background-color: #fff;
	background-color: rgba(255, 255, 255, var(--bg-opacity))
}

.w-48 {
	width: 12rem
}

.origin-top {
	transform-origin: top
}

.origin-top-right {
	transform-origin: top right
}

.origin-top-left {
	transform-origin: top left
}

.top-0 {
	top: 0
}

.right-0 {
	right: 0
}

.left-0 {
	left: 0
}

.z-50 {
	z-index: 50
}

.mt-2 {
	margin-top: .5rem
}


.rounded-md {
	border-radius: .375rem
}


@media (min-width:640px) {
	.sm\:rounded-md {
		border-radius: .375rem
	}
	.sm\:rounded-lg {
		border-radius: .5rem
	}
	/* .sm\:block {
		display: block
	} */
	.sm\:flex {
		display: flex
	}
	.sm\:items-start {
		align-items: flex-start
	}
	.sm\:items-center {
		align-items: center
	}
	.sm\:justify-start {
		justify-content: flex-start
	}
	.sm\:justify-center {
		justify-content: center
	}
	.sm\:flex-shrink-0 {
		flex-shrink: 0
	}
	.sm\:h-10 {
		height: 2.5rem
	}
	.sm\:h-24 {
		height: 6rem
	}
	.sm\:mx-0 {
		margin-left: 0;
		margin-right: 0
	}
	.sm\:mt-0 {
		margin-top: 0
	}
	.sm\:ml-4 {
		margin-left: 1rem
	}
	.sm\:max-w-sm {
		max-width: 24rem
	}
	.sm\:max-w-md {
		max-width: 28rem
	}
	.sm\:max-w-lg {
		max-width: 32rem
	}
	.sm\:max-w-xl {
		max-width: 36rem
	}
	.sm\:max-w-2xl {
		max-width: 42rem
	}
	.sm\:p-6 {
		padding: 1.5rem
	}
	.sm\:px-0 {
		padding-left: 0;
		padding-right: 0
	}
	.sm\:px-6 {
		padding-left: 1.5rem;
		padding-right: 1.5rem
	}
	.sm\:px-8 {
		padding-left: 2rem;
		padding-right: 2rem
	}
	.sm\:px-20 {
		padding-left: 5rem;
		padding-right: 5rem
	}
	.sm\:pt-0 {
		padding-top: 0
	}
	.sm\:pb-4 {
		padding-bottom: 1rem
	}
	.sm\:text-left {
		text-align: left
	}
	.sm\:w-10 {
		width: 2.5rem
	}
	.sm\:w-full {
		width: 100%
	}
	.sm\:col-span-4 {
		grid-column: span 4/span 4
	}
	.sm\:scale-95 {
		--transform-scale-x: .95;
		--transform-scale-y: .95
	}
	.sm\:scale-100 {
		--transform-scale-x: 1;
		--transform-scale-y: 1
	}
	.sm\:translate-y-0 {
		--transform-translate-y: 0
	}
}

.shadow-lg {
	box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1), 0 4px 6px -2px rgba(0, 0, 0, .05)
}


.opacity-0 {
	opacity: 0
}

.opacity-25 {
	opacity: .25
}

.opacity-75 {
	opacity: .75
}

.opacity-100 {
	opacity: 1
}


.acc-btn {
	background-color: transparent;
	background-image: none
}


.acc-btn:focus {
	outline: 1px dotted;
	outline: 5px auto -webkit-focus-ring-color
}


.acc-btn,
.acc-btn::before,
.acc-btn::after {
	box-sizing: border-box;
	border: 0 solid #d2d6dc;
}


[role=button],
button {
	cursor: pointer
}


.space-y-6>:not(template)~:not(template) {
	--space-y-reverse: 0;
	margin-top: calc(1.5rem*(1 - var(--space-y-reverse)));
	margin-bottom: calc(1.5rem*var(--space-y-reverse))
}


.bg-gray-50 {
	--bg-opacity: 1;
	background-color: #f9fafb;
	background-color: rgba(249, 250, 251, var(--bg-opacity))
}

.bg-gray-100 {
	--bg-opacity: 1;
	background-color: #f4f5f7;
	background-color: rgba(244, 245, 247, var(--bg-opacity))
}

.bg-gray-200 {
	--bg-opacity: 1;
	background-color: #e5e7eb;
	background-color: rgba(229, 231, 235, var(--bg-opacity))
}

.bg-gray-500 {
	--bg-opacity: 1;
	background-color: #6b7280;
	background-color: rgba(107, 114, 128, var(--bg-opacity))
}

.bg-gray-800 {
	--bg-opacity: 1;
	background-color: #252f3f;
	background-color: rgba(37, 47, 63, var(--bg-opacity))
}

.bg-red-100 {
	--bg-opacity: 1;
	background-color: #fde8e8;
	background-color: rgba(253, 232, 232, var(--bg-opacity))
}

.bg-red-600 {
	--bg-opacity: 1;
	background-color: #e02424;
	background-color: rgba(224, 36, 36, var(--bg-opacity))
}

.bg-indigo-50 {
	--bg-opacity: 1;
	background-color: #f0f5ff;
	background-color: rgba(240, 245, 255, var(--bg-opacity))
}

.hover\:bg-gray-50:hover {
	--bg-opacity: 1;
	background-color: #f9fafb;
	background-color: rgba(249, 250, 251, var(--bg-opacity))
}

.hover\:bg-gray-100:hover {
	--bg-opacity: 1;
	background-color: #f4f5f7;
	background-color: rgba(244, 245, 247, var(--bg-opacity))
}

.hover\:bg-gray-700:hover {
	--bg-opacity: 1;
	background-color: #374151;
	background-color: rgba(55, 65, 81, var(--bg-opacity))
}

.hover\:bg-red-500:hover {
	--bg-opacity: 1;
	background-color: #f05252;
	background-color: rgba(240, 82, 82, var(--bg-opacity))
}

.hover\:bg-blue-500:hover {
	--bg-opacity: 1;
	background-color: #3f83f8;
	background-color: rgba(63, 131, 248, var(--bg-opacity))
}

.hover\:bg-purple-500:hover {
	--bg-opacity: 1;
	background-color: #9061f9;
	background-color: rgba(144, 97, 249, var(--bg-opacity))
}

.focus\:bg-gray-50:focus {
	--bg-opacity: 1;
	background-color: #f9fafb;
	background-color: rgba(249, 250, 251, var(--bg-opacity))
}

.focus\:bg-gray-100:focus {
	--bg-opacity: 1;
	background-color: #f4f5f7;
	background-color: rgba(244, 245, 247, var(--bg-opacity))
}

.focus\:bg-indigo-100:focus {
	--bg-opacity: 1;
	background-color: #e5edff;
	background-color: rgba(229, 237, 255, var(--bg-opacity))
}

.active\:bg-gray-50:active {
	--bg-opacity: 1;
	background-color: #f9fafb;
	background-color: rgba(249, 250, 251, var(--bg-opacity))
}

.active\:bg-gray-900:active {
	--bg-opacity: 1;
	background-color: #161e2e;
	background-color: rgba(22, 30, 46, var(--bg-opacity))
}

.active\:bg-red-600:active {
	--bg-opacity: 1;
	background-color: #e02424;
	background-color: rgba(224, 36, 36, var(--bg-opacity))
}

.bg-opacity-25 {
	--bg-opacity: 0.25
}

.border-transparent {
	border-color: transparent
}

.border-gray-200 {
	--border-opacity: 1;
	border-color: #e5e7eb;
	border-color: rgba(229, 231, 235, var(--border-opacity))
}

.border-gray-300 {
	--border-opacity: 1;
	border-color: #d2d6dc;
	border-color: rgba(210, 214, 220, var(--border-opacity))
}

.border-gray-400 {
	--border-opacity: 1;
	border-color: #9fa6b2;
	border-color: rgba(159, 166, 178, var(--border-opacity))
}

.border-blue-500 {
	--border-opacity: 1;
	border-color: #3f83f8;
	border-color: rgba(63, 131, 248, var(--border-opacity))
}

.border-indigo-400 {
	--border-opacity: 1;
	border-color: #8da2fb;
	border-color: rgba(141, 162, 251, var(--border-opacity))
}

.border-purple-500 {
	--border-opacity: 1;
	border-color: #9061f9;
	border-color: rgba(144, 97, 249, var(--border-opacity))
}

.focus\:border-gray-300:focus,
.hover\:border-gray-300:hover {
	--border-opacity: 1;
	border-color: #d2d6dc;
	border-color: rgba(210, 214, 220, var(--border-opacity))
}

.focus\:border-gray-900:focus {
	--border-opacity: 1;
	border-color: #161e2e;
	border-color: rgba(22, 30, 46, var(--border-opacity))
}

.focus\:border-red-700:focus {
	--border-opacity: 1;
	border-color: #c81e1e;
	border-color: rgba(200, 30, 30, var(--border-opacity))
}

.focus\:border-blue-300:focus {
	--border-opacity: 1;
	border-color: #a4cafe;
	border-color: rgba(164, 202, 254, var(--border-opacity))
}

.focus\:border-indigo-700:focus {
	--border-opacity: 1;
	border-color: #5145cd;
	border-color: rgba(81, 69, 205, var(--border-opacity))
}

.rounded {
	border-radius: .25rem
}


.rounded-lg {
	border-radius: .5rem
}

.rounded-full {
	border-radius: 9999px
}

.border-2 {
	border-width: 2px
}

.border {
	border-width: 1px
}

.border-b-2 {
	border-bottom-width: 2px
}

.border-l-4 {
	border-left-width: 4px
}

.border-t {
	border-top-width: 1px
}

.border-r {
	border-right-width: 1px
}

.border-b {
	border-bottom-width: 1px
}

.cursor-pointer {
	cursor: pointer
}

.block {
	display: block
}

.inline-block {
	display: inline-block
}

.flex {
	display: flex
}

.inline-flex {
	display: inline-flex
}

.table {
	display: table
}

.grid {
	display: grid
}

.hidden {
	display: none
}

.flex-col {
	flex-direction: column
}

.items-center {
	align-items: center
}

.justify-end {
	justify-content: flex-end
}

.justify-center {
	justify-content: center
}

.justify-between {
	justify-content: space-between
}

.flex-shrink-0 {
	flex-shrink: 0
}

.float-left {
	float: left
}

.font-medium {
	font-weight: 500
}

.font-semibold {
	font-weight: 600
}

.font-bold {
	font-weight: 700
}

.h-4 {
	height: 1rem
}

.h-5 {
	height: 1.25rem
}

.h-6 {
	height: 1.5rem
}

.h-8 {
	height: 2rem
}

.h-9 {
	height: 2.25rem
}

.h-12 {
	height: 3rem
}

.h-16 {
	height: 4rem
}

.h-20 {
	height: 5rem
}


.p-6 {
	padding: 1.5rem
}


.px-1 {
	padding-left: .25rem;
	padding-right: .25rem
}

.py-2 {
	padding-top: .5rem;
	padding-bottom: .5rem
}

.py-3 {
	padding-top: .75rem;
	padding-bottom: .75rem
}

.px-3 {
	padding-left: .75rem;
	padding-right: .75rem
}

.py-4 {
	padding-top: 1rem;
	padding-bottom: 1rem
}

.px-4 {
	padding-left: 1rem;
	padding-right: 1rem
}

.py-5 {
	padding-top: 1.25rem;
	padding-bottom: 1.25rem
}

.px-6 {
	padding-left: 1.5rem;
	padding-right: 1.5rem
}

.py-8 {
	padding-top: 2rem;
	padding-bottom: 2rem
}

.px-8 {
	padding-left: 2rem;
	padding-right: 2rem
}

.py-10 {
	padding-top: 2.5rem;
	padding-bottom: 2.5rem
}

.py-12 {
	padding-top: 3rem;
	padding-bottom: 3rem
}

.pt-1 {
	padding-top: .25rem
}

.pt-3 {
	padding-top: .75rem
}

.pl-3 {
	padding-left: .75rem
}

.pr-4 {
	padding-right: 1rem
}

.pb-4 {
	padding-bottom: 1rem
}

.pt-5 {
	padding-top: 1.25rem
}

.pt-6 {
	padding-top: 1.5rem
}

.pt-8 {
	padding-top: 2rem
}

.pointer-events-none {
	pointer-events: none
}
</style>