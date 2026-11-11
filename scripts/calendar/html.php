<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  ?><h1>
    <t>calendar_title</t>
</h1>
<div class="event-calendar">
    <tabs (change)="this.showCalendar(event.month,event.year)" tabs="{this.monthTabs}"
        active="{this.monthTabs ? this.monthTabs[0].value : undefined}"></tabs>
    <div class="calendar">
        <div class="days" for="day of this.weekdaysAndDays">
            <div if="typeof day == 'string'" class="weekday {day} day-long">{translate(day)}</div>
            <div if="typeof day == 'string'" class="weekday {day} day-short">{translate(day+'_short')}</div>
            <div if="typeof day != 'string'" class="{day.isInMonth ? 'in-month' : 'not-in-month'} {day.weekday}">
                {day.dayNumber}

                <div if="!!day.events" for="calendarEvent of day.events">
                    <div (click)="this.selectEvent(calendarEvent)" class="calendar-event color-{calendarEvent.color}">
                        <img class="calendar-image" if="!!calendarEvent.image"
                            src="/call/image.resize?w=300&format=webp&src=/uploads/event-images/{calendarEvent.image}" />
                        <div class="calendar-event-time" if="!!calendarEvent.start_time && !!calendarEvent.end_time">
                            {calendarEvent.start_time}</div>
                        <innerhtml
                            value="{calendarEvent.calendar_text ? calendarEvent.calendar_text : calendarEvent.title}">
                        </innerhtml>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="button-box">
        <a link="/calendar-add" class="form-button">
            <t>add_calendar_entry</t>
        </a>
    </div>

    <div class="selected-event event-green" if="!!this.selectedEvent">
        <h2>{this.selectedEvent.title}</h2>

        <div class="selected-event-details">
            <div class="card">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_date</t>
                    </h3>
                    <p>{this.selectedEvent.start_date_formatted}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_time</t>
                    </h3>
                    <p>{this.selectedEvent.start_time_formatted} {this.selectedEvent.end_time_formatted ? ' - '+this.selectedEvent.end_time_formatted
                        : ''} {this.selectedEvent.end_date_formatted != this.selectedEvent.start_date_formatted ?  '('+ this.selectedEvent.end_date_formatted +')' : ''}</p>
                </div>
            </div>

            <div class="card" if="!!this.selectedEvent.property">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_property</t>
                    </h3>
                    <p><a
                            link="/property-detail/{this.selectedEvent.property.id}">{this.selectedEvent.property.address}</a><br />{this.selectedEvent.property.zip_code} {this.selectedEvent.property.city_translated}</p>
                </div>
            </div>

            <div class="card" if="!!this.selectedEvent.contact">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_contact</t>
                    </h3>
                    <p><a link="/edit-data/contact/{this.selectedEvent.property.id}">{this.selectedEvent.contact.firstname} {this.selectedEvent.contact.lastname}</a></p>
                </div>
            </div>

            <div class="card" if="!!this.selectedEvent.venue">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_venue</t>
                    </h3>
                    <p>{this.selectedEvent.venue}</p>
                </div>
            </div>

            <div class="card" if="!!this.selectedEvent.conference_url">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_conference_url</t>
                    </h3>
                    <p><a href="{this.selectedEvent.conference_url}"
                            target="_blank">{this.selectedEvent.conference_url}</a></p>
                </div>
            </div>


            <div class="card" if="!!this.selectedEvent.organizer">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_organizer</t>
                    </h3>
                    <p>{this.selectedEvent.organizer}</p>
                </div>
            </div>

            <div class="card" if="!!this.selectedEvent.video_conferencing">
                <div class="card-content">
                    <h3>
                        <t>calendar_event_video_meeting_link</t>
                    </h3>
                    <p><a target="_blank" class="form-button" href="{this.selectedEvent.video_meeting_link}"><t>join_video_meeting</t></a></p>
                </div>
            </div>
        </div>

        <div class="card description">
            <div class="card-content">
                <h3>
                    <t>calendar_event_description</t>
                </h3>
                <innerhtml value="{this.selectedEvent.description}"></innerhtml>
            </div>
        </div>

        <div class="card description">
            <div class="card-content">
                <h3>
                    <t>calendar_event_participants</t>
                </h3>
                <ul for="participant of this.selectedEvent.participants">
                    <li class="participant-status-{participant.status}">{participant.full_name} ({participant.email})
                    </li>
                </ul>
            </div>
        </div>
        <div class="button-box" >
            <button class="form-button" (click)="this.cancelEvent(this.selectedEvent)"><?= htmlentities(translate('cancel_event')) ?></button>
            <button class="form-button" if="this.selectedEvent.event_type == 'calendar_event' && this.selectedEvent.user_id == loggedInUser$.id" (click)="router$.goto('/edit-event/'+this.selectedEvent.id)"><?= htmlentities(translate('edit_event')) ?></button>
        </div>
    </div>

</div>