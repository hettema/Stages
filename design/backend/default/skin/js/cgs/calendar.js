var Calendar = new function()
{
    this.RE_NUM = /^\-?\d+$/;
    this.NUM_CENTYEAR = 30;
    this.timeComp = false;
    this.yearScroll = true;
    this.calendars = new Array();
    
    this.Error = function (message) {
        alert (message);
        return null;
    };
    
    this.newCal = function(objTarget,blTimeComp,blYearScroll)
    {
        if (!objTarget) return this.Error("Error calling the calendar: no target control specified");
        if (!objTarget.id) return this.Error("Error calling the calendar: no id specified for target control");
        if (objTarget.value == null) return this.Error("Error calling the calendar: parameter specified is not valid target control");

        this.calendars[objTarget.id] = new Array();
        this.calendars[objTarget.id]['timeComp'] = blTimeComp? true : false;
        this.calendars[objTarget.id]['yearScroll'] = blYearScroll? true : false;
        return this;
    };


    // timestamp generating function
    this.genTsmp = function (dtDatetime)
    {
        return(this.genDate(dtDatetime) + ' ' + this.genTime(dtDatetime));
    };

    // date generating function
    this.genDate = function (dtDatetime) 
    {
        return (
            (dtDatetime.getDate() < 10 ? '0' : '') + dtDatetime.getDate() + "-"
            + (dtDatetime.getMonth() < 9 ? '0' : '') + (dtDatetime.getMonth() + 1) + "-"
            + dtDatetime.getFullYear()
        );
    };

    // time generating function
    this.genTime = function (dtDatetime) 
    {
        return (
            (dtDatetime.getHours() < 10 ? '0' : '') + dtDatetime.getHours() + ":"
            + (dtDatetime.getMinutes() < 10 ? '0' : '') + (dtDatetime.getMinutes()) + ":"
            + (dtDatetime.getSeconds() < 10 ? '0' : '') + (dtDatetime.getSeconds())
        );
    };

    // timestamp parsing function
    this.parseTsmp = function(strDatetime) 
    {
        // if no parameter specified return current timestamp
        if (!strDatetime)
            return (new Date());

        // if positive integer treat as milliseconds from epoch
        if (this.RE_NUM.exec(strDatetime))
            return new Date(strDatetime);

        // else treat as date in string format
        var arrDatetime = strDatetime.split(' ');
        return this.parseTime(arrDatetime[1], this.parseDate(arrDatetime[0]));
    };

    // date parsing function
    this.parseDate = function(strDate)
    {

        var arrDate = strDate.split('-');
        var dayVal   = arrDate[0].length == 2 ? arrDate[0] : arrDate[2];
        var monthVal = arrDate[1];
        var yearVal  = arrDate[2].length == 4 ? arrDate[2] : arrDate[0];

        if (arrDate.length != 3) return cal_error ("Invalid date format: '" + str_date + "'.\nFormat accepted is dd-mm-yyyy.");
        if (!dayVal) return cal_error ("Invalid date format: '" + str_date + "'.\nNo day of month value can be found.");
        if (!monthVal) return cal_error ("Invalid date format: '" + str_date + "'.\nNo month value can be found.");
        if (!yearVal) return cal_error ("Invalid date format: '" + str_date + "'.\nNo year value can be found.");
        
        if (!this.RE_NUM.exec(dayVal)) return cal_error ("Invalid day of month value: '" + dayVal + "'.\nAllowed values are unsigned integers.");
        if (!this.RE_NUM.exec(monthVal)) return cal_error ("Invalid month value: '" + monthVal + "'.\nAllowed values are unsigned integers.");
        if (!this.RE_NUM.exec(yearVal)) return cal_error ("Invalid year value: '" + yearVal + "'.\nAllowed values are unsigned integers.");

        var dt_date = new Date();
        dt_date.setDate(1);

        if (monthVal < 1 || monthVal > 12) return cal_error ("Invalid month value: '" + monthVal + "'.\nAllowed range is 01-12.");
        dt_date.setMonth(monthVal-1);

        if (yearVal < 100) yearVal = Number(yearVal) + (yearVal < this.NUM_CENTYEAR ? 2000 : 1900);
        dt_date.setFullYear(yearVal);

        var dt_numdays = new Date(yearVal, monthVal, 0);
        dt_date.setDate(dayVal);
        if (dt_date.getMonth() != (monthVal-1)) return cal_error ("Invalid day of month value: '" + dayVal + "'.\nAllowed range is 01-"+dt_numdays.getDate()+".");

        return (dt_date)
    };

    // time parsing function
    this.parseTime = function(strTime, dtDate)
    {

        if (!dtDate) return null;
        var arr_time = String(strTime ? strTime : '').split(':');

        if (!arr_time[0]) dtDate.setHours(0);
        else if (this.RE_NUM.exec(arr_time[0]))
            if (arr_time[0] < 24) dtDate.setHours(arr_time[0]);
            else return cal_error ("Invalid hours value: '" + arr_time[0] + "'.\nAllowed range is 00-23.");
        else return cal_error ("Invalid hours value: '" + arr_time[0] + "'.\nAllowed values are unsigned integers.");

        if (!arr_time[1]) dtDate.setMinutes(0);
        else if (this.RE_NUM.exec(arr_time[1]))
            if (arr_time[1] < 60) dtDate.setMinutes(arr_time[1]);
            else return cal_error ("Invalid minutes value: '" + arr_time[1] + "'.\nAllowed range is 00-59.");
        else return cal_error ("Invalid minutes value: '" + arr_time[1] + "'.\nAllowed values are unsigned integers.");

        if (!arr_time[2]) dtDate.setSeconds(0);
        else if (this.RE_NUM.exec(arr_time[2]))
            if (arr_time[2] < 60) dtDate.setSeconds(arr_time[2]);
            else return cal_error ("Invalid seconds value: '" + arr_time[2] + "'.\nAllowed range is 00-59.");
        else return cal_error ("Invalid seconds value: '" + arr_time[2] + "'.\nAllowed values are unsigned integers.");

        dtDate.setMilliseconds(0);
        return dtDate;
    };

    this.drawCalendar = function(dtCurrentTS,targetElm)
    {
        var STR_ICONPATH    = 'template/images/calender/';
        var dtCurrent		= new Date(dtCurrentTS);
        // months as they appear in the calendar's title
        var ARR_MONTHS 		= ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
        // week day titles as they appear on the calendar
        var ARR_WEEKDAYS 	= ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
        // day week starts from (normally 0-Su or 1-Mo)
        var NUM_WEEKSTART 	= 1;
        var dtFirstDay		= new Date(dtCurrent);
            dtFirstDay.setDate(1);
            dtFirstDay.setDate(1 - (7 + dtFirstDay.getDay() - NUM_WEEKSTART) % 7);

        var dtPrevMonth		= new Date(dtCurrent);
            dtPrevMonth.setMonth((dtPrevMonth.getMonth() - 1));
        var dtNextMonth		= new Date(dtCurrent);
            dtNextMonth.setMonth((dtNextMonth.getMonth() + 1));

        var calHTML  = "<table cellspacing='1' cellpadding='3' border='0' width='100%'><tr><td colspan='7' >";
            calHTML += "<div class='cal-top-bar'><span class='prev' onclick='Calendar.drawCalendar("+dtPrevMonth.getTime()+",\""+ targetElm +"\")'><</span>"+
                       "<span class='month-year'>"+ARR_MONTHS[dtCurrent.getMonth()]+" "+dtCurrent.getFullYear() + "</span>"+
                       "<span class='next' onclick='Calendar.drawCalendar("+dtNextMonth.getTime()+",\""+ targetElm +"\")'>></span></div>"+
                       "</td></tr>";

        // Draw week day titles su to sa
            calHTML	+= "<tr>";
            
        for (var n=0; n<7; n++) {
            calHTML	+= "<td class='day-label'>"+ARR_WEEKDAYS[(NUM_WEEKSTART+n)%7]+"</td>";
        }
            calHTML	+= "</tr>";
            calHTML	+= "</td></tr>";

        var dtCurrentDay = new Date(dtFirstDay);
        while (dtCurrentDay.getMonth() == dtCurrent.getMonth() || dtCurrentDay.getMonth() == dtFirstDay.getMonth())
        {
            // print row heder
            calHTML	+= "<tr>";
            for (var dIdx = 0; dIdx < 7; dIdx++) {

                if (dtCurrentDay.getDate() == dtCurrent.getDate() &&	dtCurrentDay.getMonth() == dtCurrent.getMonth()) { // print current date
                    calHTML	+= "<td class='day-selected'>";
                } else if (dtCurrentDay.getDay() == 0 || dtCurrentDay.getDay() == 6) { // weekend days
                    calHTML	+= "<td class='week-day'>";
                } else { // print working days of current month
                    calHTML	+= "<td>";
                }
                calHTML	+= "<span onclick='Calendar.setDateValue("+dtCurrentDay.getTime() +",\""+ targetElm +"\");'";
                calHTML	+= dtCurrentDay.getMonth() == dtCurrent.getMonth() ? ">" : " class='day-other'>"; // print days of other months
                calHTML	+= dtCurrentDay.getDate()+"</span></td>";
                
                dtCurrentDay.setDate(dtCurrentDay.getDate()+1);
            }
            // print row footer
            calHTML	+= "</tr>";
        }
            calHTML	+= "</table>";
            
            if(document.getElementById('cal-window')) {
                document.getElementById('cal-window').parentNode.removeChild(document.getElementById('cal-window'));
            }
            //Draw the calendar wrapper
            var calWrapDiv = document.createElement('div');
            calWrapDiv.setAttribute('id','cal-hidden-wrap');
            calWrapDiv.setAttribute('class','cal-hidden-wrap');
            calWrapDiv.setAttribute('onclick','Calendar.close()');
            document.getElementById(targetElm).parentNode.appendChild(calWrapDiv);
            //Draw the calendar wrapper
            var calDiv = document.createElement('div');
            calDiv.setAttribute('id','cal-window');
            calDiv.setAttribute('class','cal-wrap');
            document.getElementById(targetElm).parentNode.appendChild(calDiv);
            
            document.getElementById('cal-window').innerHTML = calHTML;
            
            return null;
    };

    this.setCalendarProperties = function(propArray)
    {
        this.timeComp = propArray && propArray['timeComp'] ? propArray['timeComp'] : false;
        this.yearScroll = propArray && propArray['yearScroll'] ? propArray['yearScroll'] : true;
        return true;
    };

    this.popup = function(targetElm, strDatetime)
    {
        if (strDatetime) {
            var dtCurrent = this.parseTsmp(strDatetime);
        } else {
            var dtCurrent = this.parseTsmp(targetElm.value);
        }
        if (!dtCurrent) return false;
        this.setCalendarProperties(this.calendars[targetElm.id]);
        this.drawCalendar(dtCurrent.valueOf(),targetElm.id);
        return false;
    };
    
    this.close = function()
    {
        var calHiddenWrap = document.getElementById('cal-hidden-wrap');
        if(calHiddenWrap) {calHiddenWrap.parentNode.removeChild(calHiddenWrap);}
        var calWrap = document.getElementById('cal-window');
        if(calWrap) {calWrap.parentNode.removeChild(calWrap);}
    }
    
    this.setDateValue = function(dtSelectedTS,targetElmId)
    {
        var dtSelected	= new Date(dtSelectedTS);
        var	dtReturn	= dtSelected.getFullYear()+'-'+
                          (dtSelected.getMonth() < 9 ? '0' : '') + (dtSelected.getMonth() + 1)+"-"+
                          (dtSelected.getDate() < 10 ? '0' : '') + dtSelected.getDate();

            document.getElementById(targetElmId).value = dtReturn;
            document.getElementById(targetElmId).setAttribute("timestamp",dtSelectedTS);
           this.close();
    };
}