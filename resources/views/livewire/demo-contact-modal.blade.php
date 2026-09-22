<div>
    {{-- Demo banner — shown to all non-super-admin users --}}
    @if(auth()->check() && !auth()->user()->hasRole('Super Admin'))
        <div class="fixed top-0 inset-x-0 z-50 bg-amber-500 text-white text-sm font-medium py-2 px-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <span>You are viewing a <strong>live demo</strong>. Actions that add or change data are disabled.</span>
            </div>
            <button wire:click="show" class="shrink-0 rounded-md bg-white/20 hover:bg-white/30 px-3 py-1 text-xs font-semibold transition-colors">
                Get your own system →
            </button>
        </div>
    @endif

    {{-- Modal overlay --}}
    @if($open)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60">
            <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl" wire:click.stop>

                {{-- Header --}}
                <div class="flex items-start justify-between p-6 pb-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-600 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-teal-600 uppercase tracking-wide">Demo Mode</span>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">This action is disabled in the demo</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            The demo is read-only so every visitor sees clean data. To use this system with your own data, contact us — we will set it up for you.
                        </p>
                    </div>
                    <button wire:click="close" class="ml-4 text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6">
                    @if($submitted)
                        {{-- Success state --}}
                        <div class="flex flex-col items-center py-6 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 mb-4">
                                <svg class="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">We have received your message</h3>
                            <p class="mt-1 text-sm text-gray-500">We will get back to you within 24 hours to discuss your requirements.</p>
                            <button wire:click="close" class="mt-4 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                                Continue exploring the demo
                            </button>
                        </div>
                    @else
                        <form wire:submit="submit" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Your name <span class="text-red-500">*</span></label>
                                    <input wire:model="name" type="text" placeholder="Jane Smith"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input wire:model="email" type="email" placeholder="jane@company.com"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input wire:model="company" type="text" placeholder="Acme Exports Ltd"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input wire:model="phone" type="tel" placeholder="+91 98765 43210"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">What do you need? <span class="text-red-500">*</span></label>
                                <textarea wire:model="message" rows="3"
                                          placeholder="Briefly describe your business and what you'd like this system to do for you..."
                                          class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500"></textarea>
                                @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-center gap-3 pt-1">
                                <button type="submit"
                                        class="flex-1 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition-colors"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submit">Send message</span>
                                    <span wire:loading wire:target="submit">Sending…</span>
                                </button>
                                <button type="button" wire:click="close"
                                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 text-center">We reply within 24 hours. No spam, ever.</p>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
