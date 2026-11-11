class CalendarTag extends Tag {
    init() {
        this.loadEvents() 
        this.monthTabs = this.getNext6Months()
    }

    loadEvents() {
        return resource.action('get_events').then(result => {
            this.setAttribute('calendar_events', result)
            let currentDate = new Date(); // Aktuelles Datum und Uhrzeit
            let currentMonth = currentDate.getMonth() + 1; // Monat (0-11, daher +1 für 1-12)
            let currentYear = currentDate.getFullYear(); // Jahr

            this.showCalendar(currentMonth, currentYear);

            if (router$.params.tab) {
                const matchingEvent = result.find(event => event.id === router$.params.tab)
                if (matchingEvent) {
                    this.selectEvent(matchingEvent)
                }
            }
        })
    }

    selectEvent(event) {
        this.setAttribute('selectedEvent', event)
        this.scrollToEvent()
    }

    showCalendar(month, year) {
        this.selected_month = month 
        this.selected_year = year
        let calendarData = this.getCalendarData(month, year)
        this.setAttribute('days', calendarData.days)
        this.setAttribute('weekdaysAndDays', calendarData.weekdaysAndDays)
        this.setAttribute('weekdays', calendarData.weekdays)
    }

    getDomain(urlStr) {
        if (urlStr) {
            let url = new URL(urlStr);
            return url.hostname;
        }

    }

    cancelEvent(event) {
        if (confirm(translate('confirm_cancel_event'))) {
            resource.action('cancel_event',event).then(result=> {
                this.loadEvents().then(result=> {
                    this.showCalendar(this.selected_month, this.selected_year)
                    this.setAttribute('selectedEvent',undefined)
                })
                toast$.display(result)
            })
        }
    }

    scrollToEvent() {
        // Find the element
        var calendarElement = document.querySelector('.selected-event');

        if (calendarElement) {
            // Calculate the position to scroll to
            var offsetTop = calendarElement.getBoundingClientRect().top + window.pageYOffset - 64;

            // Scroll to the element
            let scrollElement = window
            scrollElement.scrollTo({
                top: offsetTop,
                behavior: 'smooth' // Smooth scroll
            });
        }
    }

   getCalendarData(month, year) {
    const weekdays = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

    const getFirstDayOfWeek = date => {
        const day = date.getDay();
        return new Date(date.getFullYear(), date.getMonth(), date.getDate() - (day === 0 ? 6 : day - 1));
    };

    const addDays = (date, days) => new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);

    const firstDayOfMonth = new Date(year, month - 1, 1);
    const lastDayOfMonth = new Date(year, month, 0);

    let currentDay = getFirstDayOfWeek(firstDayOfMonth);
    const weeks = [];

    // Convert event dates for easy comparison - jetzt mit Start- und Enddatum
    const eventDates = this.calendar_events.map(event => {
        return {
            ...event,
            startDate: new Date(event.event_start),
            endDate: new Date(event.event_end)
        };
    });

    let allDays = []
    while (currentDay <= lastDayOfMonth || (weeks.length < 5 || currentDay.getDay() !== 1)) {
        const days = [];

        for (let i = 0; i < 7; i++) {
            // Find events for the current day - prüfen ob der Tag zwischen Start und Ende liegt
            let events = eventDates.filter(event => {
                const dayStart = new Date(currentDay.getFullYear(), currentDay.getMonth(), currentDay.getDate());
                const dayEnd = new Date(currentDay.getFullYear(), currentDay.getMonth(), currentDay.getDate(), 23, 59, 59);
                
                // Event ist aktiv wenn: Start <= aktueller Tag <= Ende
                return event.startDate <= dayEnd && event.endDate >= dayStart;
            });

            let newDay = {
                dayNumber: currentDay.getDate(),
                weekday: weekdays[(currentDay.getDay() + 6) % 7],
                isInMonth: currentDay.getMonth() === month - 1,
                events: events // Events die an diesem Tag aktiv sind
            }
            days.push(newDay);
            allDays.push(newDay)
            currentDay = addDays(currentDay, 1);
        }

        weeks.push({ days });
    }
    let weekdaysAndDays = weekdays.concat(allDays)
    return { weeks, weekdays, days: allDays, weekdaysAndDays };
}



    translateMonthName(month) {
        // Replace this function with your actual translation logic
        const translations = {
            1: 'january',
            2: 'february',
            3: 'march',
            4: 'april',
            5: 'may',
            6: 'june',
            7: 'july',
            8: 'august',
            9: 'september',
            10: 'october',
            11: 'november',
            12: 'december',
        };

        return translate(translations[month]);
    }

    getNext6Months() {
        const today = new Date();
        const months = [];

        for (let i = 0; i < 5; i++) {
            let year = today.getFullYear();
            let month = today.getMonth() + i;

            if (month >= 12) {
                year++;
                month -= 12;
            }

            const monthName = this.translateMonthName(month + 1); // Adding 1 to the month to make it 1-based
            const monthValue = { month: month + 1, year };

            months.push({ name: `${monthName} ${year}`, value: monthValue });
        }

        return months;
    }

}