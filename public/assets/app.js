(() => {
    const csrf = window.PORTAL?.csrf || '';
    const locale = window.PORTAL?.locale || 'ru';

    const messages = {
        ru: {
            saved: 'Готово',
            error: 'Что-то пошло не так',
            reset: 'Исходные данные восстановлены',
            created: 'Встреча создана',
            createdWithSupport: 'Встреча создана. В задачах появилось новое изменение',
            reminders: 'Напоминания проверены',
            congratulated: 'Поздравление добавлено в уведомления',
            processed: 'Обработка завершена',
        },
        en: {
            saved: 'Done',
            error: 'Something went wrong',
            reset: 'Demo data restored',
            created: 'Meeting created',
            createdWithSupport: 'Meeting created. A new task update is waiting for you',
            reminders: 'Reminders checked',
            congratulated: 'Congratulations added to notifications',
            processed: 'Processing complete',
        },
    }[locale];

    function escapeHtml(value) {
        const replacements = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#039;',
            '"': '&quot;',
        };

        return String(value ?? '').replace(/[&<>'"]/g, (char) => replacements[char]);
    }

    function toast(message, isError = false) {
        const stack = document.getElementById('toast-stack');
        if (!stack) {
            return;
        }

        const item = document.createElement('div');
        item.className = 'toast' + (isError ? ' is-error' : '');
        item.innerHTML = `
            <span>${isError ? '!' : '✓'}</span>
            <div><strong>${escapeHtml(message)}</strong></div>
        `;

        stack.appendChild(item);
        setTimeout(() => item.remove(), 3400);
    }

    async function post(url, data = {}) {
        const body = new FormData();
        body.set('_token', csrf);

        for (const [key, value] of Object.entries(data)) {
            if (Array.isArray(value)) {
                value.forEach((item) => body.append(`${key}[]`, item));
                continue;
            }

            if (value !== undefined && value !== null) {
                body.set(key, String(value));
            }
        }

        const response = await fetch(url, {
            method: 'POST',
            body,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        const json = await response.json().catch(() => ({ ok: false }));
        if (!response.ok || !json.ok) {
            throw new Error(json.error || `HTTP ${response.status}`);
        }

        return json;
    }

    // Общие действия портала. Большая часть кнопок работает через делегирование,
    // чтобы после AJAX-подгрузки не перевешивать обработчики заново.
    document.addEventListener('click', async (event) => {
        const sidebarOpen = event.target.closest('[data-sidebar-open]');
        const sidebarClose = event.target.closest('[data-sidebar-close]');

        if (sidebarOpen) {
            document.getElementById('sidebar')?.classList.add('is-open');
        }
        if (sidebarClose) {
            document.getElementById('sidebar')?.classList.remove('is-open');
        }

        const reset = event.target.closest('[data-reset-demo]');
        if (reset) {
            reset.disabled = true;
            try {
                await post('/api/demo/reset');
                toast(messages.reset);
                setTimeout(() => location.reload(), 350);
            } catch (error) {
                toast(messages.error, true);
                reset.disabled = false;
            }
        }

        const employeeFavorite = event.target.closest('[data-toggle-employee-favorite]');
        if (employeeFavorite) {
            try {
                const result = await post('/api/favorites/employee', {
                    employee_id: employeeFavorite.dataset.employeeId,
                });

                employeeFavorite.classList.toggle('is-active', Boolean(result.enabled));
                employeeFavorite.textContent = result.enabled ? '★' : '☆';
                toast(messages.saved);
            } catch (error) {
                toast(messages.error, true);
            }
        }

        const departmentFavorite = event.target.closest('[data-toggle-department-favorite]');
        if (departmentFavorite) {
            try {
                await post('/api/favorites/department', {
                    department_id: departmentFavorite.dataset.departmentId,
                });
                toast(messages.saved);
                setTimeout(() => location.reload(), 250);
            } catch (error) {
                toast(messages.error, true);
            }
        }

        const congratulate = event.target.closest('[data-congratulate]');
        if (congratulate) {
            congratulate.disabled = true;
            try {
                await post('/api/birthdays/congratulate', {
                    employee_id: congratulate.dataset.employeeId,
                });
                toast(messages.congratulated);
            } catch (error) {
                toast(messages.error, true);
            } finally {
                congratulate.disabled = false;
            }
        }

        const generateReminders = event.target.closest('[data-generate-reminders]');
        if (generateReminders) {
            generateReminders.disabled = true;
            try {
                const result = await post('/api/notifications/generate');
                toast(`${messages.reminders}: ${result.created_count}`);
            } catch (error) {
                toast(messages.error, true);
            } finally {
                generateReminders.disabled = false;
            }
        }

        const shiftEvent = event.target.closest('[data-shift-event]');
        if (shiftEvent) {
            shiftEvent.disabled = true;
            try {
                const result = await post(`/api/calendar/events/${shiftEvent.dataset.eventId}/shift`);
                toast(result.task_unread_count ? (locale === 'ru' ? 'Встреча перенесена. Связанная задача обновлена' : 'Meeting moved. The linked task was updated') : messages.saved);
                setTimeout(() => location.reload(), 250);
            } catch (error) {
                toast(messages.error, true);
                shiftEvent.disabled = false;
            }
        }

        const deleteEvent = event.target.closest('[data-delete-event]');
        if (deleteEvent) {
            deleteEvent.disabled = true;
            try {
                await post(`/api/calendar/events/${deleteEvent.dataset.eventId}/delete`);
                toast(messages.saved);
                setTimeout(() => location.reload(), 250);
            } catch (error) {
                toast(messages.error, true);
                deleteEvent.disabled = false;
            }
        }

        const processNext = event.target.closest('[data-process-next]');
        if (processNext) {
            processNext.disabled = true;
            try {
                await post('/api/automation/process-next');
                toast(messages.processed);
                setTimeout(() => location.reload(), 250);
            } catch (error) {
                toast(messages.error, true);
                processNext.disabled = false;
            }
        }

        const processAll = event.target.closest('[data-process-all]');
        if (processAll) {
            processAll.disabled = true;
            try {
                const result = await post('/api/automation/process-all');
                toast(`${messages.processed}: ${result.processed_count}`);
                setTimeout(() => location.reload(), 250);
            } catch (error) {
                toast(messages.error, true);
                processAll.disabled = false;
            }
        }

        const retry = event.target.closest('[data-retry-job]');
        if (retry) {
            retry.disabled = true;
            try {
                await post(`/api/automation/jobs/${retry.dataset.jobId}/retry`);
                toast(messages.saved);
                setTimeout(() => location.reload(), 220);
            } catch (error) {
                toast(messages.error, true);
                retry.disabled = false;
            }
        }
    });

    document.addEventListener('change', async (event) => {
        const communicationSupport = event.target.closest('[data-communication-support]');
        if (communicationSupport) {
            document.querySelectorAll('[data-communication-details]').forEach((item) => {
                item.hidden = !communicationSupport.checked;
            });
        }

        const employeeReminder = event.target.closest('[data-employee-reminder]');
        if (employeeReminder) {
            try {
                await post('/api/reminders/employee', {
                    employee_id: employeeReminder.dataset.employeeId,
                    days: employeeReminder.value,
                });
                toast(messages.saved);
            } catch (error) {
                toast(messages.error, true);
            }
        }

        const departmentReminder = event.target.closest('[data-department-reminder]');
        if (departmentReminder) {
            try {
                await post('/api/reminders/department', {
                    department_id: departmentReminder.dataset.departmentId,
                    days: departmentReminder.value,
                });
                toast(messages.saved);
            } catch (error) {
                toast(messages.error, true);
            }
        }

        const channel = event.target.closest('[data-channel]');
        if (channel) {
            const portal = document.querySelector('[data-channel="portal"]')?.checked ? 1 : 0;
            const email = document.querySelector('[data-channel="email"]')?.checked ? 1 : 0;

            try {
                await post('/api/notifications/channels', { portal, email });
                toast(messages.saved);
            } catch (error) {
                toast(messages.error, true);
            }
        }
    });

    // Комментарий сначала сохраняется в самом событии. Если для встречи выбрано
    // «Сопровождение связи», после фоновой обработки он появится и в связанной задаче.
    document.querySelectorAll('[data-event-comment-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const input = form.querySelector('input[name="text"]');
            const submit = form.querySelector('button[type="submit"]');
            const comment = input?.value.trim() || '';

            if (!comment || !submit) {
                return;
            }

            submit.disabled = true;
            try {
                const result = await post(`/api/calendar/events/${form.dataset.eventId}/comments`, {
                    text: comment,
                    author_id: form.querySelector('input[name="author_id"]')?.value || 1,
                });

                const supportEnabled = form.dataset.support === '1';
                toast(
                    supportEnabled && result.task_unread_count
                        ? (locale === 'ru' ? 'Комментарий добавлен. Связанная задача обновлена' : 'Comment added. The linked task was updated')
                        : (locale === 'ru' ? 'Комментарий добавлен в событие календаря' : 'Comment added to the calendar event')
                );
                setTimeout(() => location.reload(), 350);
            } catch (error) {
                toast(messages.error, true);
                submit.disabled = false;
            }
        });
    });

    const calendarForm = document.querySelector('[data-calendar-form]');
    if (calendarForm) {
        calendarForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submit = calendarForm.querySelector('button[type="submit"]');
            if (!submit) {
                return;
            }

            submit.disabled = true;
            const data = new FormData(calendarForm);
            const payload = {
                title: data.get('title'),
                start: data.get('start'),
                end: data.get('end'),
                location: data.get('location'),
                organizer_id: data.get('organizer_id'),
                description: data.get('description'),
                attendees: data.getAll('attendees[]'),
                communication_support: data.get('communication_support') ? 1 : 0,
                simulate_failure: data.get('simulate_failure') ? 1 : 0,
            };

            try {
                const result = await post('/api/calendar/events', payload);
                if (result.communication_support && result.background_status === 'failed') {
                    toast(locale === 'ru'
                        ? 'Встреча создана. Демо-обработка остановилась на временной ошибке'
                        : 'Meeting created. Demo processing stopped on a temporary error', true);
                } else {
                    toast(result.communication_support ? messages.createdWithSupport : messages.created);
                }
                setTimeout(() => {
                    location.href = '/calendar';
                }, 450);
            } catch (error) {
                toast(`${messages.error}: ${error.message}`, true);
                submit.disabled = false;
            }
        });
    }

    // Небольшой слайдер на главной оставлен без отдельной библиотеки.
    const slider = document.querySelector('[data-home-slider]');
    if (slider) {
        const slides = Array.from(slider.querySelectorAll('[data-home-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-home-dot]'));
        let index = 0;

        const showSlide = (nextIndex) => {
            if (!slides.length) {
                return;
            }

            index = (nextIndex + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('is-active', slideIndex === index);
            });
            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === index);
            });
        };

        slider.querySelector('[data-home-prev]')?.addEventListener('click', () => showSlide(index - 1));
        slider.querySelector('[data-home-next]')?.addEventListener('click', () => showSlide(index + 1));
        dots.forEach((dot, dotIndex) => {
            dot.addEventListener('click', () => showSlide(dotIndex));
        });
    }

    document.querySelector('[data-calendar-form-focus]')?.addEventListener('click', () => {
        document.getElementById('new-meeting')?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });

        setTimeout(() => {
            document.querySelector('[data-calendar-form] input[name="title"]')?.focus();
        }, 350);
    });

    // Поздравление открывается отдельным окном: открытка, стикер и текст
    // отправляются только в журнал демо-уведомлений.
    const birthdayModal = document.querySelector('[data-congratulate-modal]');
    let birthdayEmployeeId = null;

    const resetBirthdayModal = () => {
        if (!birthdayModal) {
            return;
        }

        const message = birthdayModal.querySelector('[data-birthday-message]');
        if (message) {
            message.value = '';
            message.dispatchEvent(new Event('input'));
        }

        const firstCard = birthdayModal.querySelector('input[name="demo-card"]');
        const firstSticker = birthdayModal.querySelector('input[name="demo-sticker"]');
        if (firstCard) firstCard.checked = true;
        if (firstSticker) firstSticker.checked = true;
    };

    document.addEventListener('click', async (event) => {
        const opener = event.target.closest('[data-congratulate-open]');
        if (opener && birthdayModal) {
            birthdayEmployeeId = opener.dataset.employeeId;
            const person = birthdayModal.querySelector('[data-congratulate-person]');

            if (person) {
                person.textContent = locale === 'ru'
                    ? `Для: ${opener.dataset.employeeName || ''}`
                    : `To: ${opener.dataset.employeeName || ''}`;
            }

            resetBirthdayModal();
            birthdayModal.hidden = false;
            document.body.style.overflow = 'hidden';
            return;
        }

        if (event.target.closest('[data-congratulate-close]') && birthdayModal) {
            birthdayModal.hidden = true;
            document.body.style.overflow = '';
            birthdayEmployeeId = null;
            return;
        }

        const confirm = event.target.closest('[data-congratulate-confirm]');
        if (!confirm || !birthdayModal || !birthdayEmployeeId) {
            return;
        }

        confirm.disabled = true;
        try {
            const message = birthdayModal.querySelector('[data-birthday-message]')?.value || '';
            const card = birthdayModal.querySelector('input[name="demo-card"]:checked')?.value || '';
            const sticker = birthdayModal.querySelector('input[name="demo-sticker"]:checked')?.value || '';

            await post('/api/birthdays/congratulate', {
                employee_id: birthdayEmployeeId,
                message,
                card,
                sticker,
            });

            toast(messages.congratulated);

            // Если поздравление отправили с главной, сразу меняем кнопку на статус.
            // Та же отметка останется после обновления страницы в текущей демо-сессии.
            document.querySelectorAll(`[data-congratulate-open][data-employee-id="${birthdayEmployeeId}"]`).forEach((button) => {
                button.textContent = locale === 'ru' ? '✓ Поздравлено' : '✓ Sent';
                button.classList.add('is-sent');
                button.disabled = true;
            });

            birthdayModal.hidden = true;
            document.body.style.overflow = '';
            birthdayEmployeeId = null;
        } catch (error) {
            toast(messages.error, true);
        } finally {
            confirm.disabled = false;
        }
    });

    const birthdayMessage = document.querySelector('[data-birthday-message]');
    const birthdayMessageCount = document.querySelector('[data-birthday-message-count]');

    if (birthdayMessage && birthdayMessageCount) {
        const updateMessageCount = () => {
            birthdayMessageCount.textContent = String(birthdayMessage.value.length);
        };

        birthdayMessage.addEventListener('input', updateMessageCount);
        updateMessageCount();
    }

    document.querySelectorAll('[data-compose-carousel]').forEach((carousel) => {
        const strip = carousel.querySelector('.birthday-compose-options');

        const move = (direction) => {
            const step = Math.max(160, (strip?.clientWidth || 320) * 0.62);
            strip?.scrollBy({ left: direction * step, behavior: 'smooth' });
        };

        carousel.querySelector('[data-compose-prev]')?.addEventListener('click', () => move(-1));
        carousel.querySelector('[data-compose-next]')?.addEventListener('click', () => move(1));
    });

    // Календарь периода — отдельный компонент выбора диапазона без сторонней библиотеки.
    const rangePicker = document.querySelector('[data-birthday-range-picker]');
    const rangeForm = document.querySelector('[data-birthday-range-form]');

    if (rangePicker && rangeForm) {
        const fromInput = rangeForm.querySelector('[data-birthday-range-from]');
        const toInput = rangeForm.querySelector('[data-birthday-range-to]');
        const rangeLabel = rangeForm.querySelector('[data-birthday-range-label]');
        const monthLabel = rangePicker.querySelector('[data-range-month]');
        const daysGrid = rangePicker.querySelector('[data-range-days]');
        const trigger = rangeForm.querySelector('[data-birthday-range-open]');

        const monthNames = locale === 'ru'
            ? ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь']
            : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        const parseIso = (value) => {
            const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!match) {
                return null;
            }

            return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
        };

        const iso = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const compareDates = (a, b) => iso(a).localeCompare(iso(b));
        const todayDate = new Date();
        todayDate.setHours(0, 0, 0, 0);

        let selectedStart = parseIso(rangePicker.dataset.from);
        let selectedEnd = parseIso(rangePicker.dataset.to);
        const initialViewDate = selectedStart || todayDate;
        let viewDate = new Date(initialViewDate.getFullYear(), initialViewDate.getMonth(), 1);

        const renderRangeCalendar = () => {
            if (!daysGrid || !monthLabel) {
                return;
            }

            const yearSuffix = locale === 'ru' ? 'г.' : '';
            monthLabel.textContent = `${monthNames[viewDate.getMonth()]} ${viewDate.getFullYear()} ${yearSuffix}`.trim();
            daysGrid.innerHTML = '';

            const firstDay = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
            const lastDay = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 0);
            const leading = (firstDay.getDay() + 6) % 7;

            for (let index = 0; index < leading; index += 1) {
                const empty = document.createElement('span');
                empty.className = 'is-empty';
                daysGrid.appendChild(empty);
            }

            for (let day = 1; day <= lastDay.getDate(); day += 1) {
                const date = new Date(viewDate.getFullYear(), viewDate.getMonth(), day);
                const value = iso(date);
                const button = document.createElement('button');

                button.type = 'button';
                button.dataset.rangeDate = value;
                button.textContent = String(day);

                if (value === iso(todayDate)) {
                    button.classList.add('is-today');
                }
                if (selectedStart && value === iso(selectedStart)) {
                    button.classList.add('is-start');
                }
                if (selectedEnd && value === iso(selectedEnd)) {
                    button.classList.add('is-end');
                }
                if (
                    selectedStart
                    && selectedEnd
                    && compareDates(date, selectedStart) > 0
                    && compareDates(date, selectedEnd) < 0
                ) {
                    button.classList.add('is-in-range');
                }
                if (compareDates(date, todayDate) < 0) {
                    button.classList.add('is-past');
                }

                daysGrid.appendChild(button);
            }
        };

        trigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            rangePicker.hidden = !rangePicker.hidden;

            if (!rangePicker.hidden) {
                renderRangeCalendar();
            }
        });

        rangePicker.querySelector('[data-range-prev]')?.addEventListener('click', () => {
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
            renderRangeCalendar();
        });

        rangePicker.querySelector('[data-range-next]')?.addEventListener('click', () => {
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
            renderRangeCalendar();
        });

        daysGrid?.addEventListener('click', (event) => {
            const target = event.target.closest('[data-range-date]');
            if (!target) {
                return;
            }

            const picked = parseIso(target.dataset.rangeDate);
            if (!picked) {
                return;
            }

            if (!selectedStart || selectedEnd) {
                selectedStart = picked;
                selectedEnd = null;
            } else if (compareDates(picked, selectedStart) < 0) {
                selectedEnd = selectedStart;
                selectedStart = picked;
            } else {
                selectedEnd = picked;
            }

            renderRangeCalendar();
        });

        rangePicker.querySelector('[data-range-reset]')?.addEventListener('click', () => {
            const params = new URLSearchParams({
                mode: rangeForm.querySelector('input[name="mode"]')?.value || 'all',
                period: 'month',
            });
            location.href = '/birthdays?' + params.toString();
        });

        rangePicker.querySelector('[data-range-apply]')?.addEventListener('click', () => {
            if (!selectedStart) {
                return;
            }

            selectedEnd ||= selectedStart;
            if (fromInput) {
                fromInput.value = iso(selectedStart);
            }
            if (toInput) {
                toInput.value = iso(selectedEnd);
            }
            if (rangeLabel) {
                rangeLabel.textContent = `${iso(selectedStart)} — ${iso(selectedEnd)}`;
            }

            rangeForm.submit();
        });

        document.addEventListener('click', (event) => {
            if (!rangePicker.hidden && !rangeForm.contains(event.target)) {
                rangePicker.hidden = true;
            }
        });
    }

    const emailPreviewModal = document.querySelector('[data-email-preview-modal]');
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-email-preview-open]') && emailPreviewModal) {
            emailPreviewModal.hidden = false;
            document.body.style.overflow = 'hidden';
            return;
        }

        if (event.target.closest('[data-email-preview-close]') && emailPreviewModal) {
            emailPreviewModal.hidden = true;
            document.body.style.overflow = '';
        }
    });

    const phonebookTabs = document.querySelector('[data-phonebook-tabs]');
    if (phonebookTabs) {
        const tabs = Array.from(phonebookTabs.querySelectorAll('[data-phonebook-tab]'));
        const activate = (name) => {
            tabs.forEach((tab) => {
                tab.classList.toggle('is-active', tab.dataset.phonebookTab === name);
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab.dataset.phonebookTab));
        });

        if (location.hash === '#structure') {
            activate('departments');
        }
    }

    // Поиск сотрудников и алфавит работают отдельно. При обычном текстовом поиске
    // буква сбрасывается, иначе было бы непонятно, почему часть результатов исчезла.
    const searchForm = document.querySelector('[data-employee-search-form]');
    const searchInput = document.querySelector('[data-employee-search-input]');
    const departmentSelect = document.querySelector('[data-employee-department]');
    const letterInput = document.querySelector('[data-employee-letter-input]');
    const resultsContainer = document.querySelector('[data-employee-results]');
    const loadMoreButton = document.querySelector('[data-employee-load-more]');
    const loadMoreWrap = document.querySelector('[data-load-more-wrap]');
    const visibleCount = document.querySelector('[data-visible-count]');
    const totalCount = document.querySelector('[data-total-count]');
    const pageSize = Number(loadMoreButton?.dataset.limit || 10);

    let searchTimer = null;
    let activeSearchTerms = [];
    let currentOffset = Number(loadMoreButton?.dataset.offset || resultsContainer?.children.length || 0);

    function escapeRegExp(value) {
        return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlightText(value, terms = activeSearchTerms) {
        const source = String(value ?? '');
        const cleanTerms = [
            ...new Set(
                (terms || [])
                    .map((term) => String(term).trim())
                    .filter(Boolean)
            ),
        ].sort((a, b) => b.length - a.length);

        if (!cleanTerms.length) {
            return escapeHtml(source);
        }

        const regex = new RegExp(cleanTerms.map(escapeRegExp).join('|'), 'giu');
        let html = '';
        let last = 0;

        for (const match of source.matchAll(regex)) {
            const index = match.index ?? 0;
            html += escapeHtml(source.slice(last, index));
            html += `<mark class="phonebook-highlight">${escapeHtml(match[0])}</mark>`;
            last = index + match[0].length;
        }

        html += escapeHtml(source.slice(last));
        return html;
    }

    function renderEmployeeCard(employee, terms = activeSearchTerms) {
        const initials = String(employee._name || '?')
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => Array.from(part)[0] || '')
            .join('');

        let hint = '';
        if (employee._vacation) {
            hint = locale === 'ru' ? 'В отпуске' : 'On vacation';
        } else if (employee._match && employee._match !== 'all') {
            const label = locale === 'ru' ? 'Нашлось по' : 'Matched by';
            hint = `${label}: ${escapeHtml(employee._match)}`;
        }

        return `
            <article class="phonebook-employee-row" data-employee-card>
                <a class="phonebook-employee-name" href="/employees/${Number(employee.id)}">
                    <span class="avatar">${escapeHtml(initials)}</span>
                    <span>
                        <strong>${highlightText(employee._name, terms)}</strong>
                        ${hint ? `<small>${hint}</small>` : ''}
                    </span>
                </a>
                <span class="phonebook-cell phonebook-phone">${highlightText(employee.phone || '', terms)}</span>
                <span class="phonebook-cell">${highlightText(employee._position || '', terms)}</span>
                <span class="phonebook-cell">${highlightText(employee._department || '', terms)}</span>
            </article>
        `;
    }

    function updateLoadMoreState(total, nextOffset, hasMore) {
        currentOffset = nextOffset;

        if (loadMoreButton) {
            loadMoreButton.dataset.offset = String(nextOffset);
        }
        if (visibleCount) {
            visibleCount.textContent = String(nextOffset);
        }
        if (totalCount) {
            totalCount.textContent = String(total);
        }
        if (loadMoreWrap) {
            loadMoreWrap.hidden = !hasMore;
        }
    }

    async function fetchEmployeePage(offset = 0, append = false) {
        if (!searchForm || !searchInput || !resultsContainer) {
            return;
        }

        const params = new URLSearchParams({
            q: searchInput.value,
            department: departmentSelect?.value || '0',
            letter: letterInput?.value || '',
            offset: String(offset),
            limit: String(pageSize),
        });

        try {
            const response = await fetch('/api/employees/search?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();

            if (!data.ok) {
                return;
            }

            activeSearchTerms = data.search_terms || [];
            const html = data.results
                .map((employee) => renderEmployeeCard(employee, activeSearchTerms))
                .join('');

            if (append) {
                resultsContainer.insertAdjacentHTML('beforeend', html);
            } else {
                resultsContainer.innerHTML = html;
            }

            const resultCount = document.querySelector('[data-results-count]');
            if (resultCount) {
                resultCount.textContent = data.total;
            }

            updateLoadMoreState(data.total, data.next_offset, data.has_more);

            if (!append) {
                const historyParams = new URLSearchParams({
                    q: searchInput.value,
                    department: departmentSelect?.value || '0',
                });

                if (letterInput?.value) {
                    historyParams.set('letter', letterInput.value);
                }

                history.replaceState({}, '', '/employees?' + historyParams.toString());
            }
        } catch (error) {
            // Поиск не должен ломать страницу, если демо-сервер на секунду недоступен.
        }
    }

    async function runEmployeeSearch() {
        currentOffset = 0;
        await fetchEmployeePage(0, false);
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            if (searchInput.value.trim() !== '' && letterInput) {
                letterInput.value = '';
                document
                    .querySelectorAll('[data-employee-alphabet] a.is-active')
                    .forEach((link) => link.classList.remove('is-active'));
            }

            clearTimeout(searchTimer);
            searchTimer = setTimeout(runEmployeeSearch, 220);
        });
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', runEmployeeSearch);
    }

    if (searchForm) {
        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();
            runEmployeeSearch();
        });
    }

    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', async () => {
            loadMoreButton.disabled = true;
            await fetchEmployeePage(currentOffset, true);
            loadMoreButton.disabled = false;
        });
    }
})();
