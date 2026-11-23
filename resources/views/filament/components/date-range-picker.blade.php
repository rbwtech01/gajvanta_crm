@php
    $periodPath = $getStatePath();
    $prefix = str_contains($periodPath, '.') ? \Illuminate\Support\Str::beforeLast($periodPath, '.') . '.' : '';
    $fromPath = $prefix . 'from';
    $toPath = $prefix . 'to';
@endphp

<div
    wire:ignore
    class="drp"
    x-data="{
        paths: { period: @js($periodPath), from: @js($fromPath), to: @js($toPath) },

        // committed state (what goes to Livewire)
        period: null,
        from: null,
        to: null,

        // display range (what the calendar highlights)
        ds: null,
        de: null,
        picking: false,
        view: new Date(new Date().getFullYear(), new Date().getMonth(), 1),

        weekStartsOn: 1, // preview only; 1 = Monday (Carbon default), 0 = Sunday

        presets: [
            { key: 'custom', label: 'Custom' },
            { key: 'today', label: 'Today' },
            { key: 'yesterday', label: 'Yesterday' },
            { key: 'this_week', label: 'This week' },
            { key: 'last_7_days', label: 'Last 7 days' },
            { key: 'last_14_days', label: 'Last 14 days' },
            { key: 'this_month', label: 'This month' },
            { key: 'last_30_days', label: 'Last 30 days' },
            { key: 'last_month', label: 'Last month' },
            { key: 'this_quarter', label: 'This quarter' },
            { key: 'this_year', label: 'This year' },
            { key: 'all', label: 'All time' },
        ],
        // presets that exist in the backend; the others are sent as custom from/to
        backendPresets: ['all','today','yesterday','this_week','last_7_days','this_month','last_month','this_quarter','this_year'],

        weekdays: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
        months: ['January','February','March','April','May','June','July','August','September','October','November','December'],

        init() {
            this.period = this.$wire.$get(this.paths.period) || null
            this.from = this.norm(this.$wire.$get(this.paths.from))
            this.to = this.norm(this.$wire.$get(this.paths.to))

            if (this.period) {
                const r = this.presetRange(this.period)
                this.ds = r.start
                this.de = r.end
            } else {
                this.ds = this.from
                this.de = this.to
            }

            if (this.ds) {
                this.view = new Date(this.ds + 'T00:00:00')
                this.view.setDate(1)
            }
        },

        norm(v) { return v ? String(v).substring(0, 10) : null },

        iso(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0')
        },

        fmt(v) {
            if (!v) return ''
            const p = v.split('-')
            return p[2] + '/' + p[1] + '/' + p[0]
        },

        presetRange(key) {
            const t = new Date(); t.setHours(0, 0, 0, 0)
            const y = t.getFullYear(), m = t.getMonth()
            let s = null, e = null
            const addDays = (d, n) => { const c = new Date(d); c.setDate(c.getDate() + n); return c }

            switch (key) {
                case 'today': s = t; e = t; break
                case 'yesterday': s = addDays(t, -1); e = s; break
                case 'this_week': {
                    const diff = (t.getDay() - this.weekStartsOn + 7) % 7
                    s = addDays(t, -diff); e = addDays(s, 6); break
                }
                case 'last_7_days': s = addDays(t, -6); e = t; break
                case 'last_14_days': s = addDays(t, -13); e = t; break
                case 'last_30_days': s = addDays(t, -29); e = t; break
                case 'this_month': s = new Date(y, m, 1); e = new Date(y, m + 1, 0); break
                case 'last_month': s = new Date(y, m - 1, 1); e = new Date(y, m, 0); break
                case 'this_quarter': { const q = Math.floor(m / 3) * 3; s = new Date(y, q, 1); e = new Date(y, q + 3, 0); break }
                case 'this_year': s = new Date(y, 0, 1); e = new Date(y, 11, 31); break
            }
            return { start: s ? this.iso(s) : null, end: e ? this.iso(e) : null }
        },

        // single place that writes to Livewire (flat keys only, no request until Apply)
        commit(period, from, to) {
            this.period = period
            this.from = from
            this.to = to
            this.$wire.$set(this.paths.period, period, false)
            this.$wire.$set(this.paths.from, from, false)
            this.$wire.$set(this.paths.to, to, false)
        },

        active() {
            if (this.period) return this.period
            for (const k of ['last_14_days', 'last_30_days']) {
                const r = this.presetRange(k)
                if (this.from === r.start && this.to === r.end) return k
            }
            return 'custom'
        },

        selectPreset(key) {
            this.picking = false

            if (key === 'custom') {
                this.commit(null, this.ds, this.de)
                return
            }

            const r = this.presetRange(key)
            this.ds = r.start
            this.de = r.end

            if (this.backendPresets.includes(key)) {
                this.commit(key, null, null)          // preset: clear from/to
            } else {
                this.commit(null, r.start, r.end)     // 14/30 days: custom range
            }

            if (r.start) {
                this.view = new Date(r.start + 'T00:00:00')
                this.view.setDate(1)
            }
        },

        selectDate(iso) {
            if (!this.picking) {
                this.ds = iso
                this.de = null
                this.picking = true
                this.commit(null, iso, null)          // custom: clear period
            } else {
                let a = this.ds, b = iso
                if (b < a) { const x = a; a = b; b = x }
                this.ds = a
                this.de = b
                this.picking = false
                this.commit(null, a, b)
            }
        },

        prev() { this.view = new Date(this.view.getFullYear(), this.view.getMonth() - 1, 1) },
        next() { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + 1, 1) },

        cells() {
            const y = this.view.getFullYear(), m = this.view.getMonth()
            const lead = new Date(y, m, 1).getDay()
            const count = new Date(y, m + 1, 0).getDate()
            const out = []
            for (let i = 0; i < lead; i++) out.push({ key: 'e' + i, empty: true })
            for (let d = 1; d <= count; d++) {
                out.push({ key: 'd' + d, empty: false, day: d, iso: this.iso(new Date(y, m, d)) })
            }
            return out
        },

        cellClass(c) {
            if (c.empty) return ''
            const range = this.ds && this.de && this.ds !== this.de
            return {
                'drp-band': range && c.iso > this.ds && c.iso < this.de,
                'drp-band-r': range && c.iso === this.ds,
                'drp-band-l': range && c.iso === this.de,
            }
        },

        dayClass(c) {
            return {
                'drp-sel': c.iso === this.ds || c.iso === this.de,
                'drp-today': c.iso === this.iso(new Date()),
            }
        },
    }"
>
    <style>
        .drp {
            --drp-bg: #ffffff;
            --drp-border: #e5e7eb;
            --drp-text: #374151;
            --drp-muted: #6b7280;
            --drp-hover: #f3f4f6;
            --drp-band: color-mix(in srgb, var(--primary-500, #f59e0b) 18%, transparent);
            --drp-accent: var(--primary-600, #d97706);
            --drp-input: #ffffff;
        }
        .dark .drp {
            --drp-bg: rgba(255, 255, 255, 0.03);
            --drp-border: rgba(255, 255, 255, 0.1);
            --drp-text: #d1d5db;
            --drp-muted: #9ca3af;
            --drp-hover: rgba(255, 255, 255, 0.06);
            --drp-band: color-mix(in srgb, var(--primary-500, #f59e0b) 25%, transparent);
            --drp-input: rgba(255, 255, 255, 0.05);
        }

        .drp-box {
            display: flex;
            width: 100%;
            max-width: 100%;
            border: 1px solid var(--drp-border);
            border-radius: 0.75rem;
            background: var(--drp-bg);
            color: var(--drp-text);
            overflow: hidden;
        }
        .drp-presets {
            flex: 0 0 9.5rem;
            border-right: 1px solid var(--drp-border);
            padding: 0.5rem 0;
        }
        .drp-preset {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            text-align: left;
            font-size: 0.875rem;
            color: var(--drp-text);
            background: transparent;
            border: 0;
            cursor: pointer;
        }
        .drp-preset:hover { background: var(--drp-hover); }
        .drp-preset.is-active {
            color: var(--drp-accent);
            font-weight: 600;
            background: var(--drp-band);
        }

        .drp-main { flex: 1 1 auto; min-width: 0; padding: 1rem; }

        .drp-inputs { display: flex; align-items: flex-end; gap: 0.5rem; margin-bottom: 1rem; }
        .drp-inputs > div { flex: 1 1 0; min-width: 0; }
        .drp-label { display: block; margin-bottom: 0.25rem; font-size: 0.75rem; color: var(--drp-muted); }
        .drp-input {
            width: 100%;
            padding: 0.4rem 0.6rem;
            font-size: 0.875rem;
            color: var(--drp-text);
            background: var(--drp-input);
            border: 1px solid var(--drp-border);
            border-radius: 0.5rem;
        }
        .drp-sep { padding-bottom: 0.45rem; color: var(--drp-muted); }

        .drp-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
        .drp-title { font-weight: 600; font-size: 0.9rem; }
        .drp-nav {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2rem; height: 2rem; border: 0; border-radius: 0.5rem;
            background: transparent; color: var(--drp-text); cursor: pointer;
        }
        .drp-nav:hover { background: var(--drp-hover); }

        /* 7-column grid: scoped CSS, independent of Tailwind compilation */
        .drp-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            row-gap: 2px;
        }
        .drp-wd { padding: 0.35rem 0; text-align: center; font-size: 0.75rem; color: var(--drp-muted); }
        .drp-cell { position: relative; display: flex; justify-content: center; height: 2.25rem; }
        .drp-band { background: var(--drp-band); }
        .drp-band-r { background: linear-gradient(to right, transparent 50%, var(--drp-band) 50%); }
        .drp-band-l { background: linear-gradient(to left, transparent 50%, var(--drp-band) 50%); }
        .drp-day {
            position: relative; z-index: 1;
            width: 2.25rem; height: 2.25rem;
            border: 0; border-radius: 9999px;
            background: transparent; color: var(--drp-text);
            font-size: 0.875rem; cursor: pointer;
        }
        .drp-day:hover { background: var(--drp-hover); }
        .drp-day.drp-today { font-weight: 700; color: var(--drp-accent); }
        .drp-day.drp-sel,
        .drp-day.drp-sel:hover { background: var(--drp-accent); color: #fff; font-weight: 600; }

        @media (max-width: 640px) {
            .drp-box { flex-direction: column; }
            .drp-presets {
                flex: none; display: flex; flex-wrap: wrap; gap: 0.25rem;
                border-right: 0; border-bottom: 1px solid var(--drp-border); padding: 0.5rem;
            }
            .drp-preset { width: auto; padding: 0.3rem 0.6rem; border-radius: 9999px; font-size: 0.8rem; }
            .drp-day { width: 2rem; height: 2rem; }
            .drp-cell { height: 2rem; }
        }
    </style>

    <div class="drp-box">
        {{-- Presets --}}
        <div class="drp-presets">
            <template x-for="p in presets" :key="p.key">
                <button
                    type="button"
                    class="drp-preset"
                    :class="{ 'is-active': active() === p.key }"
                    @click="selectPreset(p.key)"
                    x-text="p.label"
                ></button>
            </template>
        </div>

        {{-- Calendar --}}
        <div class="drp-main">
            <div class="drp-inputs">
                <div>
                    <span class="drp-label">Start date</span>
                    <input type="text" readonly class="drp-input" placeholder="dd/mm/yyyy" :value="fmt(ds)">
                </div>
                <span class="drp-sep">—</span>
                <div>
                    <span class="drp-label">End date</span>
                    <input type="text" readonly class="drp-input" placeholder="dd/mm/yyyy" :value="fmt(de)">
                </div>
            </div>

            <div class="drp-head">
                <button type="button" class="drp-nav" @click="prev()" aria-label="Previous month">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="drp-title" x-text="months[view.getMonth()] + ' ' + view.getFullYear()"></div>
                <button type="button" class="drp-nav" @click="next()" aria-label="Next month">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <div class="drp-grid">
                <template x-for="w in weekdays" :key="w">
                    <div class="drp-wd" x-text="w"></div>
                </template>
            </div>

            <div class="drp-grid">
                <template x-for="c in cells()" :key="c.key">
                    <div class="drp-cell" :class="cellClass(c)">
                        <button
                            type="button"
                            class="drp-day"
                            x-show="!c.empty"
                            :class="c.empty ? '' : dayClass(c)"
                            @click="c.empty || selectDate(c.iso)"
                            x-text="c.empty ? '' : c.day"
                        ></button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>