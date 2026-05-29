<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.$entangle(@js($getStatePath())),
            pad: null,
            init() {
                const canvas = this.$refs.canvas;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                canvas.width = canvas.parentElement.offsetWidth * ratio;
                canvas.height = canvas.parentElement.offsetHeight * ratio;

                this.pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)',
                });

                if (this.state) {
                    this.pad.fromDataURL(this.state, { ratio: 1 });
                }

                this.pad.addEventListener('endStroke', () => {
                    this.state = this.pad.toDataURL();
                });
            },
            clear() {
                this.pad.clear();
                this.state = null;
            },
        }"
        wire:ignore
        class="space-y-2"
    >
        <div
            class="relative rounded-lg border border-gray-300 bg-white dark:border-gray-600 overflow-hidden"
            style="height: 200px;"
        >
            <canvas
                x-ref="canvas"
                class="w-full h-full touch-none"
            ></canvas>
        </div>

        <button
            type="button"
            x-on:click="clear()"
            class="inline-flex items-center rounded-lg bg-danger-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-danger-500 focus:outline-none"
        >
            გასუფთავება
        </button>
    </div>
</x-dynamic-component>
