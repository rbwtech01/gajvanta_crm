(() => {
    const unsupportedPresets = new Set([
        'today',
        'yesterday',
        'this_week',
        'last_week',
        'this_quarter',
        'all',
    ]);

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    const startOfWeek = (date) => {
        const result = new Date(date);
        const day = result.getDay();
        const daysFromMonday = (day + 6) % 7;
        result.setDate(result.getDate() - daysFromMonday);
        result.setHours(0, 0, 0, 0);

        return result;
    };

    const getRange = (preset) => {
        const today = new Date();
        const start = new Date(today);
        const end = new Date(today);

        switch (preset) {
            case 'today':
                break;
            case 'yesterday':
                start.setDate(start.getDate() - 1);
                end.setDate(end.getDate() - 1);
                break;
            case 'this_week':
                return [startOfWeek(today), end];
            case 'last_week': {
                const thisWeek = startOfWeek(today);
                const lastWeekStart = new Date(thisWeek);
                lastWeekStart.setDate(lastWeekStart.getDate() - 7);
                const lastWeekEnd = new Date(thisWeek);
                lastWeekEnd.setDate(lastWeekEnd.getDate() - 1);

                return [lastWeekStart, lastWeekEnd];
            }
            case 'this_quarter': {
                const quarterStartMonth = Math.floor(today.getMonth() / 3) * 3;
                const quarterStart = new Date(today.getFullYear(), quarterStartMonth, 1);
                const quarterEnd = new Date(today.getFullYear(), quarterStartMonth + 3, 0);

                return [quarterStart, quarterEnd];
            }
            case 'all':
                return [null, null];
            default:
                return null;
        }

        start.setHours(0, 0, 0, 0);
        end.setHours(0, 0, 0, 0);

        return [start, end];
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.fi-date-range-picker-preset');

        if (!button) {
            return;
        }

        const preset = button.querySelector('[x-text]')?.textContent.trim().toLowerCase();
        const presetKey = {
            'today': 'today',
            'yesterday': 'yesterday',
            'this week (mon - today)': 'this_week',
            'last week (mon - sun)': 'last_week',
            'this quarter': 'this_quarter',
            'all time': 'all',
        }[preset];

        if (!unsupportedPresets.has(presetKey)) {
            return;
        }

        const pickerElement = button.closest('[x-data]');
        const picker = pickerElement && window.Alpine?.$data(pickerElement);
        const range = getRange(presetKey);

        if (!picker || !range) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        picker.activePreset = presetKey;
        picker.state = {
            start: range[0] ? formatDate(range[0]) : null,
            end: range[1] ? formatDate(range[1]) : null,
        };
    }, true);
})();
