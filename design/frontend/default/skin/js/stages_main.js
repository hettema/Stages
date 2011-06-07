/**
 * Object holds and controls all the data and requests with in the system
 */
function Stages()
{
    this.callBackAction = false;
    this._people = new Array();
    this._bcHost;
    this._bcProjects = new Array();
    this._bcMilestoneIndex = new Array();
    
    var self = this;

    this.getUrl = function(path){return window.location.protocol +"//"+ window.location.host +"/"+ (!path ? '' : path);};

    this.getRequest = function()
    {
        if(!this.request) {this.request = new Request();}
        return this.request;
    };

    this.reqServer = function(strUri, dataObj, responseFn)
    {
        this.getRequest().reqServer(this.getUrl(strUri),dataObj, responseFn);        
    };    
    
    this.loadBcInfo = function()
    {
        this.reqServer('stages/user/get_bc_info', {}, this.afterLoadBcInfo);        
    };
    
    this.afterLoadBcInfo = function(response)
    {
        if(!self.validateResponse(response) || !response.bc_host) { return false; }
        self._bcHost = response.bc_host;
    };        
    
    this.getBcHost = function() { return this._bcHost; }
    
    this.loadPeople = function(callBackAction)
    {
        this.callBackAction = callBackAction;
        this.reqServer('stages/user/people', {}, this.afterLoadPeople);
    };

    this.afterLoadPeople = function(response)
    {
        if(!self.validateResponse(response) || !response.people) { return false; }

        self._bcPeople = response.people;
        if(self.callBackAction) {
            eval(self.callBackAction);
            self.callBackAction = false;
        }
    };

    this.loadProjects = function()
    {
        this.reqServer('stages/user/projects', {}, this.afterLoadProjects);
    };

    this.afterLoadProjects = function(response)
    {
        if(!self.validateResponse(response) || !response.projects) {
            return false;
        }
        self._bcProjects = response.projects;
    };

    this.loadMilestoneIndex = function()
    {
        this.reqServer('stages/user/milestone_index', {}, this.afterLoadMilestoneIndex);
    };

    this.afterLoadMilestoneIndex = function(response)
    {
        if(!self.validateResponse(response) || !response.ms_index) {
            return false;
        }
        self._bcMilestoneIndex = response.ms_index;
    };

    this.searchPeople = function (request,response)
    {
        var data = [];
        var people = self._bcPeople;
        for(var idx =0; idx < people.length;idx++) {
            var term = request.term.replace(/^\s*/, "").replace(/\s*$/, "");
            var name = people[idx].firstname +' '+ people[idx].lastname;
            if (name.toLowerCase().indexOf(term.toLowerCase()) != -1) {
                data.push({name:name, value:name, firstname:people[idx].firstname, lastname:people[idx].lastname, bc_id:people[idx].bc_id});
                if (data.length > 5){break;}
            }
        }
        response(data)
    }

    this.searchProject = function (request,response)
    {
        var data = [];
        for(var idx =0; idx < self._bcProjects.length;idx++) {
            var term = request.term.replace(/^\s*/, "").replace(/\s*$/, "");
            var title = self._bcProjects[idx].title;
            if (title.toLowerCase().indexOf(term.toLowerCase()) != -1) {
                data.push({name:title, value:title,  bc_id:self._bcProjects[idx].bc_id});
                if (data.length > 6){break;}
            }
        }
        response(data)
    }
    
    this.searchMilestone = function (request,response)
    {
        var data = [];
        var miles = self._bcMilestoneIndex;
        for(var idx in miles) {
            var term = request.term.replace(/^\s*/, "").replace(/\s*$/, "");
            var milestone = miles[idx];
            if (milestone.toLowerCase().indexOf(term.toLowerCase()) != -1) {
                data.push({name:milestone, value:milestone});
                if (data.length > 5){break;}
            }
        }
        response(data)
    };

    this.searchMilestoneType = function (request,response)
    {
        response(['m','d']);
    }


    this.getUser = function(q, field)
    {
        field = !field ? 'id' : field;
        for(var idx =0; idx < this._bcPeople.length;idx++) {
            if(this._bcPeople[idx][field] && this._bcPeople[idx][field] == q) {
                return this._bcPeople[idx];
            }
        }
        return false;
    }

    this.getUserByName = function(name)
    {
        for(var idx =0; idx < this._bcPeople.length;idx++) {
            if(this._bcPeople[idx].firstname +' '+ this._bcPeople[idx].lastname == name) {
                return this._bcPeople[idx];
            }
        }
        return false;
    };

    this.getAvatar = function(bc_id)
    {
        var user = this.getUser(bc_id, 'bc_id');
        if(user && user.bc_avatar) {return user.bc_avatar;}
        return 'https://asset0.37img.com/global/missing/avatar.png?r=3';
    };

    this.getUserName = function(bc_id)
    {
        var user = this.getUser(bc_id, 'bc_id');
        if(user) {return user.firstname +' '+ user.lastname;}
        return false;
    }
    
    this.validateResponse = function(response)
    {
        if(response && response.redirect) {
            window.location.replace(response.redirect);
            return false;
        }
        if(!response || response.success == 0) {return false;}
        return true;
    };

    
}

/**
 * Object manages request que within the application
 */
function Request()
{
    this.reqQue = new Array();
    this.reqLoading = false;
    this.timer = false;

    var self = this;
    this.reqServer = function(strUrl, dataObj, responseFn)
    {
        if(!$('#req-indicator').length) {
            $('#footer').append('<div id="req-indicator" style="position: absolute; top: 40px; right: 4px; padding: 3px; background: #0a843e; color: #DADADA; font-weight: bold;">Working...</div>');
        }
        if(this.reqLoading) {
            this.addToQue({url:strUrl, data:dataObj, success:responseFn});
        } else {
            this.reqLoading = true;
            $.ajax({type: 'POST', url: strUrl, data:dataObj, success: responseFn, complete: this.afterReqServer, dataType: 'json'});
        }

    };

    this.afterReqServer = function(response)
    {
        self.reqLoading = false;
        $('#req-indicator').remove();
        self.processQue();
    };

    this.addToQue = function(reqObj)
    {
        this.reqQue.push(reqObj);
        this.timer = setTimeout(this.processReqQue, 200);
    };

    this.processReqQue = function() { self.processQue(); }

    this.processQue = function()
    {
        if(this.reqQue.length > 0) {
            if(!this.reqLoading) {
                var req = this.reqQue.shift();
                this.reqServer(req.url, req.data, req.success);
            }
            this.timer = setTimeout(this.processReqQue, 200);
        } else {
            clearTimeout(this.timer);
        }
        return;
    }

    
}


