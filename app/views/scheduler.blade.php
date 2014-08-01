@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
  <div class="col-md-3">
	<div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a>TASKS</a>
            </h4>
          </div>
          <div class="panel-collapse">
            <ul class="list-group">
              <li class="list-group-item"><a data-toggle="collapse" href="#collapseEligible">Eligible Tasks</a><span class="badge">4</span>
                <ul class="list-group collapse" id="collapseEligible">
                  <li class="list-group-item">Business Cards<span class="glyphicon glyphicon-search pull-right"></span></li>

                  <li class="list-group-item">Postcards</li>

                  <li class="list-group-item">Newsletters</li>

                  <li class="list-group-item">Annual Reports</li>
                </ul>
              </li>
              <li class="list-group-item"><a data-toggle="collapse" href="#collapseUnplaced" class="">Unplaced Tasks</a><span class="badge">4</span>
                <ul class="list-group collapse" id="collapseUnplaced">
                  <li class="list-group-item">Business Cards</li>

                  <li class="list-group-item">Postcards</li>

                  <li class="list-group-item">Newsletters</li>

                  <li class="list-group-item">Annual Reports</li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
  </div>
  <div class="col-md-9">  <h1>Week Calendar Demo</h1>

  <p class="description">
    This calendar demonstrates the differents new options that allow user
    management and freebusy display / computation.
  </p>

  <div id="message" class="ui-corner-all"></div>

  <div id="calendar_selection" class="ui-corner-all">
    <strong>Event Data Source: </strong>
    <select id="data_source">
      <option value="">Select Event Data</option>
      <option value="1">Event Data 1</option>
      <option value="2">Event data 2</option>
    </select>
  </div>

  <div id="calendar"></div>
  </div>
</div>

@stop

{{-- Javascript --}}
@section('javascript')
  <script type="text/javascript">
  (function($) {
    var d = new Date();
    d.setDate(d.getDate() - d.getDay());
    var year = d.getFullYear();
    var month = d.getMonth();
    var day = d.getDate();

    var eventData1 = {
      options: {
        timeslotsPerHour: 4,
        timeslotHeight: 20,
        defaultFreeBusy: {free: true}
      },
      events : [
        {'id':1, 'start': new Date(year, month, day, 12), 'end': new Date(year, month, day, 13, 30), 'title': 'Lunch with Mike', userId: 0},
        {'id':2, 'start': new Date(year, month, day, 14), 'end': new Date(year, month, day, 14, 45), 'title': 'Dev Meeting', userId: 1},
        {'id':3, 'start': new Date(year, month, day+1, 18), 'end': new Date(year, month, day+1, 18, 45), 'title': 'Hair cut', userId: 1},
        {'id':4, 'start': new Date(year, month, day+2, 8), 'end': new Date(year, month, day+2, 9, 30), 'title': 'Team breakfast', userId: 0},
        {'id':5, 'start': new Date(year, month, day+1, 14), 'end': new Date(year, month, day+1, 15), 'title': 'Product showcase', userId: 1}
      ],
      freebusys: [
        {'start': new Date(year, month, day), 'end': new Date(year, month, day+3), 'free': false, userId: [0,1,2,3]},
        {'start': new Date(year, month, day, 8), 'end': new Date(year, month, day, 12), 'free': true, userId: [0,1,2,3]},
        {'start': new Date(year, month, day+1, 8), 'end': new Date(year, month, day+1, 12), 'free': true, userId: [0,1,2,3]},
        {'start': new Date(year, month, day+2, 8), 'end': new Date(year, month, day+2, 12), 'free': true, userId: [0,1,2,3]},
        {'start': new Date(year, month, day+1, 14), 'end': new Date(year, month, day+1, 18), 'free': true, userId: [0,1,2,3]},
        {'start': new Date(year, month, day+2, 8), 'end': new Date(year, month, day+2, 12), 'free': true, userId: [0,3]},
        {'start': new Date(year, month, day+2, 14), 'end': new Date(year, month, day+2, 18), 'free': true, userId: 1}
      ]
    };

    $(document).ready(function() {
      var $calendar = $('#calendar').weekCalendar({
        timeslotsPerHour: 4,
        scrollToHourMillis : 0,
        height: function($calendar){
          return $(window).height() - $('h1').outerHeight(true) - 200;
        },
        eventRender : function(calEvent, $event) {
          if (calEvent.end.getTime() < new Date().getTime()) {
            $event.css('backgroundColor', '#aaa');
            $event.find('.wc-time').css({
              backgroundColor: '#999',
              border:'1px solid #888'
            });
          }
        },
        eventNew : function(calEvent, $event, FreeBusyManager, calendar) {
          var isFree = true;
          $.each(FreeBusyManager.getFreeBusys(calEvent.start, calEvent.end), function() {
            if (
              this.getStart().getTime() != calEvent.end.getTime()
              && this.getEnd().getTime() != calEvent.start.getTime()
              && !this.getOption('free')
            ){
              isFree = false;
              return false;
            }
          });

          if (!isFree) {
            alert('looks like you tried to add an event on busy part !');
            $(calendar).weekCalendar('removeEvent',calEvent.id);
            return false;
          }

          alert('You\'ve added a new event. You would capture this event, add the logic for creating a new event with your own fields, data and whatever backend persistence you require.');

          calEvent.id = calEvent.userId +'_'+ calEvent.start.getTime();
          $(calendar).weekCalendar('updateFreeBusy', {
            userId: calEvent.userId,
            start: calEvent.start,
            end: calEvent.end,
            free:false
          });
        },
        data: function(start, end, callback) {
            callback(eventData1);
        },
        users: ['SM52', 'CD74', 'Screen', 'CD102'],
        showAsSeparateUser: true,
        displayOddEven: true,
        displayFreeBusys: true,
        daysToShow: 1,
        use24Hour: true,
        headerSeparator: ' ',
        useShortDayNames: true,
        // I18N
        dateFormat: 'F d, y'
      });
    });
  })(jQuery);
  </script>
@stop
