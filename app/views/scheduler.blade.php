@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row" id="calendar-row">
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
                <ul class="list-group in" id="collapseEligible">
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
        timeslotsPerHour: 1,
        timeslotHeight: 60,
        defaultFreeBusy: {free: true}
      },
      events : [
        {'id':1, 'start': new Date(year, month, day, 12), 'end': new Date(year, month, day, 13, 0), 'title': 'Lunch with Mike', userId: 0},
        {'id':2, 'start': new Date(year, month, day, 14), 'end': new Date(year, month, day, 15, 0), 'title': 'Dev Meeting', userId: 1},
        {'id':3, 'start': new Date(year, month, day+1, 18), 'end': new Date(year, month, day+1, 19, 0), 'title': 'Hair cut', userId: 1},
        {'id':4, 'start': new Date(year, month, day+2, 8), 'end': new Date(year, month, day+2, 9, 0), 'title': 'Team breakfast', userId: 0},
        {'id':5, 'start': new Date(year, month, day+1, 14), 'end': new Date(year, month, day+1, 15), 'title': 'Product showcase', userId: 1}
      ]
    };

    $(document).ready(function() {
      var $calendar = $('#calendar').weekCalendar({
        timeslotsPerHour: 4,
        scrollToHourMillis : 0,
        height: function($calendar){
          return $(window).height() - $('h1').outerHeight(true) - 0;
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
      $( ".list-group-item" ).draggable({
        cursor: "pointer",
        helper: function(){
          return $('<div class="wc-cal-event ui-corner-all" id="hi" style="line-height: 15px; font-size: 13px; height: 60px; display: block; background-color: rgb(170, 170, 170);"><div class="wc-time ui-corner-top" style="border: 1px solid rgb(136, 136, 136); background-color: rgb(153, 153, 153);">08:00: Team breakfast<div class="wc-cal-event-delete ui-icon ui-icon-close"></div></div><div class="wc-title">Team breakfast</div><div class="ui-resizable-handle ui-resizable-s">=</div></div>');
        },
        grid: [0, 60],
        scroll: "false",
        appendTo: "div.wc-user-0",
        stop: function(event, ui){
          console.log(ui.helper);
          console.log($(event.srcElement).position().top);
          // console.log($(ui.helper).css({top: 15}).attr('id', 'testing'));
          // console.log(ui.offset.top);
        },
        drag: function(event, ui){
          console.log($(ui.helper));
        }
      });
    });
  })(jQuery);
  </script>
@stop
