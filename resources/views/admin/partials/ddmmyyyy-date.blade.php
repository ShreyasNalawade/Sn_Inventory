@once
    <style>
        .app-date-control {
            position: relative;
        }

        .app-date-text {
            padding-right: 2.2rem;
            background-color: #fff;
        }

        .app-date-native {
            position: absolute;
            inset: 0;
            z-index: 2;
            width: 100%;
            height: 100%;
            margin: 0;
            opacity: 0;
            cursor: pointer;
        }

        .app-date-icon {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            z-index: 1;
            color: #64748b;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .app-date-caption {
            display: block;
            margin-top: 0.25rem;
            min-height: 1rem;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .app-date-overlay .app-date-caption {
            position: absolute;
            top: calc(100% + 0.15rem);
            left: 0;
            right: 0;
            margin-top: 0;
        }

        @media (max-width: 991.98px) {
            .app-date-overlay {
                margin-bottom: 1.15rem;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            const formatStoredDate = (iso) => {
                const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
                if (!match) {
                    return null;
                }

                const year = Number(match[1]);
                const month = Number(match[2]);
                const day = Number(match[3]);
                const date = new Date(year, month - 1, day);

                if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
                    return null;
                }

                return {
                    short: `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`,
                    long: `${day} ${months[month - 1]} ${year}`,
                };
            };

            document.querySelectorAll('.app-date-native').forEach((input) => {
                const text = document.getElementById(`${input.id}-display`);
                const caption = document.getElementById(`${input.id}-caption`);

                const sync = () => {
                    const formatted = formatStoredDate(input.value);
                    if (text) {
                        text.value = formatted ? formatted.short : '';
                    }
                    if (caption) {
                        caption.textContent = formatted ? `(${formatted.long})` : '';
                    }
                };

                input.addEventListener('change', sync);
                input.addEventListener('input', sync);
                sync();
            });
        });
    </script>
@endonce

<div class="app-date {{ !empty($overlay) ? 'app-date-overlay' : '' }}">
    <div class="app-date-control">
        <input type="text" class="form-control app-date-text" id="{{ $id }}-display"
            placeholder="dd/mm/yyyy" readonly tabindex="-1" autocomplete="off">
        <input type="date" class="app-date-native" id="{{ $id }}" name="{{ $name }}"
            value="{{ $value ?? '' }}" @if(!empty($required)) required @endif>
        <span class="app-date-icon" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
    </div>
    <span class="app-date-caption" id="{{ $id }}-caption"></span>
</div>
