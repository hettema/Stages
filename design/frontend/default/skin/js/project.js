/**
 * Projecttimeline object, controls the project edit and view interfaces
 */
function ProjectTimeline()
{
    this._canEdit = true;
    this._startDate = false;
    this._endDate = false;
    this._pDuration = 27;
    this._allowMultipleMs = false;
    this._projects = 0;
    this._devLeads = new Array();
    this._markLeads = new Array();
    this._developers = new Array();
    this._marketingExecutives = new Array();
    this._project = false;
    
    this._weekStartDay = 1;
    this._projectStartDay = 1; // starts on monday by default
    this._weekDays = new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    this._weekDaysShort = new Array('S','M','T','W','T','F','S');
    this._showSunday = true;
    var self = this;

    this.init = function()
    {
        //$('#tl_start').datepicker({onSelect: self.pickStartDate});
        //$('#tl_end').datepicker({onSelect: self.pickEndDate});
        //$('#timeline header h2').click(function() { $('#tl_start').datepicker('show'); });
        //$('#timeline footer h2').click(function() {$('#tl_end').datepicker('show');});
        stages.loadBcInfo();
        stages.loadPeople();
        stages.loadProjects();
        stages.loadMilestoneIndex();
        this.makeFieldAutocomplete('pname', 'project', this.loadProject);
        this.makeFieldAutocomplete('mlead', 'people', this.saveLead);
        this.makeFieldAutocomplete('dlead', 'people', this.saveLead);

        this.project = {}
        this.project.milestones = new Array();
    };

    this.loadProject = function(project)
    {
        if(!project.bc_id) {alert('unable to load the project');return false;};

        $('#timeline ul').remove();
        stages.reqServer('project/index/load', {project_bc_id:project.bc_id}, self.setProject);
    };

    this.setProject = function(response)
    {
        if(!stages.validateResponse(response) || !response.project) {
            self.setStartDate(false, true); //reset date and redraw timeline
            $('#tw').find('.btn_create a').html('<span class="bc"></span>Create This project on basecamp');
            return false;
        }
        self.project = response.project; 
        if(self.project.start_date) {self.setStartDate(self.project.start_date);}
        if(self.project.end_date) {self.setEndDate(self.project.end_date);}
        if(self.project.leads && self.project.leads['m']) {$('#mlead').val(stages.getUserName(self.project.leads['m']));}
        if(self.project.leads && self.project.leads['d']) {$('#dlead').val(stages.getUserName(self.project.leads['d']));}
        $('#tw').find('.btn_create a').html('<span class="bc"></span>Update Project');
        //project view page
        $('#prjct').find('h1').text(self.project.title);
        $('#bc_link').attr('href', self.project.bc_link);
        self.drawProjectTimeline();
    };
    
    this.pickStartDate = function(dStart, inst) {self.setStartDate(dStart, true);}
    this.pickEndDate = function(dEnd, inst) {self.setEndDate(dEnd, true);}

    this.setStartDate = function(dStart, redraw)
    {
        this._startDate = !dStart ? new Date() : this.getDateObject(dStart);
        if(this._startDate.getDay != 1) {
            this._startDate.setDate(this._startDate.getDate() - this._startDate.getDay() +1);
        }
        this.setEndDate();
        $('#timeline header h2').text(this.getDisplayDate(this._startDate));
        if(redraw) {
            this.drawProjectTimeline();
        }
        return this;
    };

    this.setEndDate = function(dEnd, redraw)
    {
        if(!dEnd) {
            this._endDate = new Date();
            this._endDate.setTime(this._startDate.getTime());
            this._endDate.setDate(this._startDate.getDate() + this._pDuration);
        } else {
            this._endDate = this.getDateObject(dEnd);
        }
        if(this._endDate.getDay() ==0) {this._endDate.setDate(this._endDate.getDate() - 1);}
        if(this._endDate.getDay != 6) {
            this._endDate.setDate(this._endDate.getDate() + (6 - this._endDate.getDay()));
        }
        $('#timeline footer h2').text(this.getDisplayDate(this._endDate));
        if(redraw) {
            this.drawProjectTimeline();
        }
        return this;
    };

    this.drawProjectTimeline = function()
    {
        $('#week_items ul').remove();
        var sApi = $('.scrollable').data("scrollable");
        if(!sApi) {
            $('.scrollable').scrollable({easing: 'swing', speed: 300, circular: false, keyboard: 'static' });
        } else {
            sApi.begin(200);
        }
        
        var dCurr = new Date(this._startDate.getTime());
        var weekIdx = 0;
        while(dCurr.getTime() <= this._endDate.getTime()) {
            if(weekIdx == 0 || dCurr.getDay() == this._weekStartDay) {
                weekIdx += 1;
                var weekWrap = this.drawWeekColumn(dCurr);
            }
            dCurr.setDate(dCurr.getDate() + 1);
        }
         this.enableToolTips();
    };

    this.enableToolTips = function()
    {
        $('dl.ms dt a[title], dl.ms[title], span.mday[title]').qtip({content: {text: false},
                                      style: {name: 'light',background: '#888',padding: 1,textAlign: 'center',color: '#fff',border: {width: 1,radius: 3,color: '#888'}, tip: 'topLeft'},
                                      show: {delay:0},
                                      position: {adjust: {x:-10}}
                                    });
    };
    
    this.addWeek = function()
    {           
        var dStart = new Date(this._endDate.getTime());
        var dDiff = this._weekStartDay - dStart.getDay() + 7;
            dStart.setDate(dStart.getDate() + dDiff);
        if(this._showSunday) { //add sunday to the last week collumn
            var dSunLast = new Date(this._endDate.getTime() + 86400000);
            $('#week_items').find('ul.week:last').append(this.drawDateColumn(dSunLast));
            
        }
        this.setEndDate(this.getDisplayDate(dStart), false);
        this.drawWeekColumn(dStart);
        
        this.enableToolTips();
        return false;
    };
    
    this.drawWeekColumn = function(dStart, dEnd)
    {
        if(!dStart || dStart == 'undefined') {
            dStart = new Date(this._endDate.getTime());
            dStart.setDate(dStart.getDate() + 2);
        }
        if(!dEnd || dEnd == 'undefined') {
            dEnd = new Date(dStart.getTime());
            dEnd.setDate(dEnd.getDate() + 6);
        }
        if(dEnd > this._endDate) {
            dEnd = new Date(this._endDate.getTime());
        }
        var weekWrap = $('<ul class="week"></ul>');
            
        //draw the days
        var dCurr = new Date(dStart.getTime());
        while(dCurr.getTime() <= dEnd.getTime()) {
            if(!this._showSunday && (dCurr.getDay() == 0)) {dCurr.setDate(dCurr.getDate() + 1);continue;}

            weekWrap.append(this.drawDateColumn(dCurr));
            dCurr.setDate(dCurr.getDate() + 1);
        }
        
        var api = $('.scrollable').data("scrollable");
            api.addItem(weekWrap);
            if(api.getSize() > 4) {
                api.seekTo(api.getSize() -4, 300);
            }
        
         $(weekWrap).find('.mrkt dt').bind('click', self.editMilestone);
         $(weekWrap).find('.dev dt').bind('click', self.editMilestone);
        return weekWrap;
    };

    this.drawDateColumn = function(dDate)
    {
        var dShort = this._weekDaysShort[dDate.getDay()];
        var milestones = this.getMilestonesForDate(dDate);
        var liCl = dDate.getDay() == 0 ? 'we' :
                                dDate.getDay() == 6 ? 'last we' :
                                dDate.toDateString() == new Date().toDateString() ? 'today' : '';
        var strHtml = '<li id="'+ this.getDateKey(dDate) +'" class="'+ liCl +'"><span class="mday" title="'+ dDate.toDateString() +'">'+ dShort +'</span>';
            strHtml += '<div class="mrkt">'+ this.getMilestoneHtml(milestones.m) +'</div>';
            strHtml += '<div class="dev">'+ this.getMilestoneHtml(milestones.d) +'</div>';
            strHtml += '<span class="dday">'+ dShort +'</span></li>';
        return strHtml;
    };

    this.getMilestoneHtml = function(milestone)
    {
        if(!milestone || !milestone.ms_user) {return '<dl class="ms"><dt></dt><dd></dd></dl>';}

        var h = 0;
        var dlCs = 'non';
        var statsStr = '';
        var titleShort = milestone.title.length <= 12 ? milestone.title : (milestone.title.substr(0, 11) + '...');
        var typeStr = milestone.type == 'd' ? 'dev' : 'marketing'
        if(milestone.todo_stats && milestone.todo_stats.lists > 0) {
            var stats = milestone.todo_stats
            h = (stats.uncompleted * 100)/ 20;
            h = h > 80 ? 80 : h;
            var completed = stats.count - stats.completed;
            if(stats.count) {
                dlCs = stats.count >0 && stats.uncompleted == 0 ? 'fin' :
                                        stats.uncompleted >0 && stats.completed > 0 ? 'start' : 'non';
            }
            if(dlCs == 'non' && (stats.comments> 0 || stats.hours> 0) ) {
                dlCs = 'start';
            }
            statsStr += 'Todo lists - '+ stats.lists +'<br/>Todos total - '+ stats.count +'<br/>Completed - '+ stats.completed +'<br/>Uncompleted - '+ stats.uncompleted +'<br/>Comments - '+ stats.comments +'<br/>Hours - '+ stats.hours;
        }
        
        return '<span class="ms_t">'+ titleShort +'<img src="'+ stages.getUrl('design/frontend/default/skin/img/timeline/'+ typeStr +'/point.png') +'"></span><img class="avatar" src="'+ stages.getAvatar(milestone.ms_user) +'" width="21" height="21" alt="Dev Milestone assigned to '+ stages.getUserName(milestone.ms_user) +'" /><dl class="ms '+ dlCs +'" title="'+ statsStr +'"><dt><a href="javascript:void(0)" onclick="projectTL.viewMilestoneOnBc('+milestone.bc_id+')" title="'+ milestone.title +' : '+ milestone.ms_date +'">'+ milestone.title +':'+ milestone.ms_date +'</a></dt><dd style="height:'+h+'%;"></dd></dl>';
    };

    this.getMilestone = function(date, type, returnIdx)
    {
        for(var k =0; k< this.project.milestones.length; k++) {
            if(type && this.project.milestones[k].type != type) {continue;}
            if(this.project.milestones[k].ms_date != this.getDisplayDate(date)) {continue;}
            if(returnIdx) {return k;}
            return this.project.milestones[k];
        }
        return false;
    };

    this.getMilestonesForDate = function(dDate)
    {
        var milestones = new Array();milestones['d'] = false;milestones['m'] = false;
        for(var k =0; k< this.project.milestones.length; k++) {
            if(this.project.milestones[k].ms_date != this.getDisplayDate(dDate)) {continue;}
            milestones[this.project.milestones[k].type] = this.project.milestones[k];
        }
        return milestones;
    };

    this.editMilestone = function(evt)
    {
        if(evt && evt.target) {
            var type = $(evt.target).closest('div').attr('class');
            var date = self.getDateFromKey($(evt.target).closest('li').attr('id'));
            
            $(evt.target).closest('li').addClass('ms_editing');
        }
        type = (type == 'mrkt') ? 'm' : 'd';
        return self.showMilestoneEdit(date, type, evt.target);
    };

    this.showMilestoneEdit = function (date, type, elm)
    {
        if(!this._canEdit) {return false;}
        var data = this.getMilestone(date, type);

        this.hideMilestoneEdit(); //$('div.qtip.qtip-light.qtip-active').remove();
        var content = '<form id="qform" action="javascript:projectTL.saveMilestone()">';
            content += '<input type="hidden" value="'+ this.getDisplayDate(date) +'" name="ms_date_old" id="msdateold" />';
            content += '<input type="hidden" value="'+ type +'" name="ms_type_old" id="mstypeold" />';
            content += '<div class="col"><label for="msdate">Date</label><input id="msdate" class="text date" style="width:70px;" /></div>';
            content += '<div class="col"><label for="mstitle">Milestone</label><input id="mstitle" class="text slct" style="width:150px;" /></div>';
            content += '<div class="col"><label for="mstype">Type</label><input id="mstype" class="text slct" style="width:40px;" value="'+ type +'" /></div>';
            content += '<input type="submit" value="save" style="position:absolute; left:-100000px;"/>';
            content += '</form></div>';

        var _qTip_tip = type == 'd' ? 'topLeft' : 'bottomLeft';
        var _qTip_y = type == 'd' ? 2 : -80;
        $(elm).qtip({
            content: content,
            style: {width: 340, name: 'light', background: '#888', padding: 1, textAlign: 'center', color: '#fff', border: {width: 1, radius: 3, color: '#888'},tip: _qTip_tip /*Notice the corner value is identical to the previously mentioned positioning corners */},
            show: {delay:200, ready: true},
            hide: {when: {event: 'inactive'}},
            position: {adjust: {y:_qTip_y, x:-10}},
            api: {onHide: self.hideMilestoneEdit}
        });
        
        if(data && data.title) {
            $('#mstitle').val(data.title);
        }
        $('#mstitle').focus()
        $('#msdate').datepicker();
        $('#msdate').datepicker('setDate', date);

        this.makeFieldAutocomplete('mstitle', 'milestone');
        this.makeFieldAutocomplete('mstype', 'milestone_type');
    };

    this.hideMilestoneEdit = function()
    {
        var parent = $('div.qtip.qtip-light').closest('dt');
        if(parent.qtip) {
            parent.qtip('destroy');
        }
        $('div.qtip.qtip-light').remove();
    };
    
    this.saveMilestone = function()
    {
        var msTitle = $('#mstitle').val();
        var msDate = $('#msdate').val();
        var msType = $('#mstype').val();
        var msTypeOld = $('#mstypeold').val();
        var msDateOld = $('#msdateold').val();
        var date = this.getDateObject(msDate);
        var oldDate = this.getDateObject(msDateOld);
        var msOld = this.getMilestone(oldDate, msTypeOld);

        if((!msDate || !msTitle) && msOld && msOld.title) {
            if(confirm('Do you want to remove the selected milestone dated '+ msDateOld +' ?')) {
                this.deleteMilestone(msDateOld, msTypeOld);
                this.hideMilestoneEdit();
            }
            return;
        } else {
            var milestone = this.addMilestone(date, msType, msTitle, oldDate, msTypeOld);
            if(!milestone) {
                return; 
            }

            $('#'+ this.getDateKey(oldDate)).removeClass('ms_editing');
        }
        this.hideMilestoneEdit();
    };

    this.deleteMilestone = function(date, type)
    {
        var msIdx = this.getMilestone(date,type,true);
        if(msIdx !== false && msIdx >= 0) {
            this.project.milestones.splice(msIdx, 1);
        }
        var divCl = type == 'd' ? 'dev' : 'mrkt';
        var liId = self.getDateKey(date);
        var div = $('#'+ liId).find('div.'+ divCl).html(this.getMilestoneHtml());
        $('#'+ liId).find('dt').bind('click', self.editMilestone);
        return false;
    };

    this.addMilestone = function(msDate, msType, msTitle, oldDate, oldType)
    {
        var msExisting = this.getMilestone(msDate, msType);
        if(msExisting && !this._allowMultipleMs) { alert('Cannot save milestone!. Another milestone exists on the same date'); return false; }
        var msObj = this.getMilestone(oldDate, oldType);
            msObj = !msObj ? {} : msObj;
            msObj.title = msTitle;
            msObj.ms_date = this.getDisplayDate(msDate);
            msObj.type = msType
        if(!msObj.ms_user) {
            var lead = this.getSelectedLead(msType);
            if(!lead) {alert("please select Leads for the project");return false;}
            msObj.ms_user = lead.bc_id;
        }
        if(!this.project || !this.project.bc_id) { alert("please select a project");return false;}
        
        this.deleteMilestone(msDate, msType); //delete previous ms in the current date selected
        this.deleteMilestone(oldDate, oldType); //delete ms in the previous date selected

        //this.project.milestones.push(msObj);
        stages.reqServer('project/create/save_milestone', {project_id:this.project.bc_id, title:msObj.title, date:msObj.ms_date, user:msObj.ms_user, type:msObj.type, bc_id:msObj.bc_id}, self.afterAddMilestone);
        
        var divCl = oldType == 'd' ? 'dev' : 'mrkt';
        $('#'+ this.getDateKey(oldDate)).find('div.'+ divCl).html(this.getMilestoneHtml(false));
        $('#'+ this.getDateKey(oldDate)).find('dt').bind('click', self.editMilestone);
        
        this.enableToolTips();
        return msObj;
    };
    
    this.afterAddMilestone = function(response)
    {
        if(!stages.validateResponse(response) || !response.milestone) { return false; }
        
        self.project.milestones.push(response.milestone);
        var divCl = response.milestone.type == 'd' ? 'dev' : 'mrkt';
        var dtId = self.getDateKey(self.getDateObject(response.milestone.ms_date));
        $('#'+dtId).find('div.'+ divCl).html(self.getMilestoneHtml(response.milestone));
        $('#'+dtId).find('dt').bind('click', self.editMilestone);
        self.enableToolTips();
        return false;
    }

    this.getSelectedLead = function(type)
    {
        var leadName = type == 'd' ? $('#dlead').val() : $('#mlead').val();
        return stages.getUserByName(leadName);
    }

    this.saveLead = function()
    {
        if(!self.project || !self.project.bc_id) { return false; }
        var pMLead = $.trim($('#mlead').val());
        var pDLead = $.trim($('#dlead').val());
        var pMLeadUser = stages.getUserByName(pMLead);
        var pDLeadUser = stages.getUserByName(pDLead);
        if(!pDLeadUser || !pMLeadUser) {return false;}
        var data = {project_id:self.project.bc_id};
        
        if(pMLeadUser) { data.m_lead = pMLeadUser.bc_id; }
        if(pDLeadUser) { data.d_lead = pDLeadUser.bc_id; }
        
        stages.reqServer('project/create/save_lead', data, self.afterSaveLead);
        return false;
        
    };
    
    this.afterSaveLead = function(response)
    {
        if(!stages.validateResponse(response) || !response.milestone) { return false; }
    };

    this.makeFieldAutocomplete = function(elmId, type, onSelect)
    {
        if(!type) {type ='people';}
        switch (type)
        {
            case 'people':
                var sourceFn = window.stages.searchPeople;
            break;
            case 'project':
                var sourceFn = window.stages.searchProject;
            break;
            case 'milestone':
                var sourceFn = window.stages.searchMilestone;
            break;
            break;
            case 'milestone_type':
                var sourceFn = window.stages.searchMilestoneType;
            break;

        }
        if(!onSelect) {onSelect = function(){}};
        $('input#'+ elmId).autocomplete({
            source : sourceFn,
            selectFirst: true,
            minLength: 0,
            select : function(event,ui) {onSelect(ui.item);}
        }).focus(function(){
            $(this).autocomplete("search")
        });
        $('input#'+ elmId).live("autocompleteopen", function() {
                var autocomplete = $(this).data("autocomplete"),
                menu = autocomplete.menu;
                if (!autocomplete.options.selectFirst ) {return;}

                menu.activate($.Event({type: "mouseenter"}), menu.element.children().first());

        });
    };

    this.saveProject = function()
    {
        var pName = $.trim($('#pname').val());
        var pMLead = $.trim($('#mlead').val());
        var pDLead = $.trim($('#dlead').val());

        if(!pName || pName == '') {alert('Please choose a name for your project');return false;}
        if(!pMLead || pMLead == '') {alert('Please choose a Marketig lead for the project');return false;}
        if(!pDLead || pDLead == '') {alert('Please choose a Developing lead for the project');return false;}

        var pMLeadUser = stages.getUserByName(pMLead);
        if(!pMLeadUser) {alert('Error in finding the Marketig lead');return false;}
        var pDLeadUser = stages.getUserByName(pDLead);
        if(!pDLeadUser) {alert('Error in finding the Developing lead');return false;}

        this.project.title = pName;
        this.project.leads = {m:pMLeadUser.bc_id, d:pDLeadUser.bc_id};
        $('#project_edit_form').append('<input type="hidden" value="'+ pName +'" name="project[title]" />');
        $('#project_edit_form').append('<input type="hidden" value="'+ pMLeadUser.bc_id +'" name="project[m_lead]" />');
        $('#project_edit_form').append('<input type="hidden" value="'+ pDLeadUser.bc_id +'" name="project[d_lead]" />');
        $('#project_edit_form').append('<input type="hidden" value="'+ this.getDisplayDate(this._startDate) +'" name="project[d_start]" />');
        $('#project_edit_form').append('<input type="hidden" value="'+ this.getDisplayDate(this._endDate) +'" name="project[d_end]" />');
        if(this.project.bc_id) {
            $('#project_edit_form').append('<input type="hidden" name="project[bc_id]" value="'+ this.project.bc_id +'" />');
        }

        for(var k =0; k < this.project.milestones.length; k++) {
            var ms = this.project.milestones[k];
            $('#project_edit_form').append('<input type="hidden" name="project[milestones]['+ k +'][title]" value="'+ ms.title +'" />');
            $('#project_edit_form').append('<input type="hidden" name="project[milestones]['+ k +'][date]" value="'+ ms.ms_date +'" />');
            $('#project_edit_form').append('<input type="hidden" name="project[milestones]['+ k +'][user]" value="'+ ms.ms_user +'" />');
            $('#project_edit_form').append('<input type="hidden" name="project[milestones]['+ k +'][type]" value="'+ ms.type +'" />');
            if(ms.bc_id) {
                $('#project_edit_form').append('<input type="hidden" name="project[milestones]['+ k +'][bc_id]" value="'+ ms.bc_id +'" />');
            }
        }
        $('#project_edit_form').submit();
        return false;
        $.post(stages.getUrl('project/create/save'), {project:JSON.stringify(this.project)}, {success: this.afterSaveProject},'json');
        
    };

    this.afterSaveProject = function(response)
    {
        alert(response);
    }
    
    this.getDateObject = function(strDate)
    {
        if(strDate.indexOf('-') > 0 ) {var spliter = '-';}
        else if(strDate.indexOf('/') > 0 ) {var spliter = '/';}
        if(!spliter) {return false;}
        
        var arrDate = strDate.split(spliter);
        var monthVal = arrDate[0].length <= 2 ? arrDate[0] : arrDate[2];
            monthVal = monthVal == 0 ? 11 : monthVal - 1;  //get month returns months 0-11
        var dayVal = arrDate[1];
        var yearVal  = arrDate[2].length == 4 ? arrDate[2] : arrDate[0];
        return new Date(yearVal, monthVal, dayVal);
    };

    this.getDisplayDate = function(dDate)
    {
        return (dDate.getMonth()+1) +'/'+ dDate.getDate() +'/'+ dDate.getFullYear();
    }

    this.getDateKey = function(dDate)
    {
        return '_'+ dDate.getMonth() +'_'+ dDate.getDate() +'_'+ dDate.getFullYear();
    };

    this.getDateFromKey = function(keyStr)
    {
        var params = keyStr.split('_');
        if(params.length < 3) {return false;}
        return new Date(params[3], params[1], params[2]);
    };
    
    this.viewMilestoneOnBc = function(bc_id)
    {
        window.open(stages.getBcHost()+'/projects/'+ this.project.bc_id +'/milestones/'+ bc_id +'/comments');
    }
}