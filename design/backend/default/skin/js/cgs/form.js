/** jsFormValidator Version 2.49 Modified on 13-08-2009*/
function HelperForm()
{
    this.formId;
    this.formName;
    this.formElements = new Array();
    this.elementTags = new Array('INPUT', 'TEXTAREA', 'SELECT', 'BUTTON' );
    
    this.setFormId = function(formId) {this.formId = formId;};
    this.setFormName = function(formName) {this.formName= formName;};

    this.setForm = function(formId)
    {
        this.reset();
        if(!document.getElementById(formId)) {           
            return false;
        }
        this.form = document.getElementById(formId);
        //return if the form is already initialized
        //if(this.formId && this.formId == this.form.id) { return true; }

        this.setFormId(this.form.id);
        this.setFormName(this.form.name);
        //Set the form elements
        for(var eIdx = 0; eIdx < this.form.length; eIdx++) {
            var elm = this.form.elements[eIdx];
            if(!elm.name) continue; 
            //if(!inArray(this.elementTags, elm.nodeName.toUpperCase())) continue;

            var valType = this.isElementValidated(elm);
            var valReq = this.isElementRequired(elm);
            this.setFormElemnt({elmnt:elm, type:valType, required:valReq, label:elm.title});
        }
        return true;
    };

    this.isElementRequired = function(elm)
    {
        var clNames = elm.className.split(' ');
        for(var cIdx in clNames) {
            if(clNames[cIdx].indexOf('required') > -1) {
                return true;
            }
        }
        return false;
    };
    
    this.isElementValidated = function(elm)
    {
        var clNames = elm.className.split(' ');
        for(var cIdx in clNames) {
            var clName = clNames[cIdx];
            if(clName.indexOf('required') > -1) { 
                return clName.split('required')[1];
            }
            if(clName.indexOf('verify') > -1) {
                return clName.split('verify')[1];
            } 
        }
        return 'text';
    };

    // Marks mandatory fields in a form with asteriks(*'s)
    this.markFieldsMandatory = function (formId) 
    {
        var formElmts = this.getFormElements();
        
        for (i = 0;i < formElmts.length; i++) {
            if(formElmts[i].required) {
                formElmts[i].elmnt.parentNode.innerHTML  += "<span class='mandatory'>*</span>";
            }  
        }
        return true;
    };    

    this.getFormElements = function() 
    { 
        return this.formElements; 
    };
    
    this.setFormElemnt = function(elmProperties)
    {
        this.formElements[this.formElements.length] = elmProperties
    };
    
    this.reset = function()
    {
        this.formId = this.formName = false;
        this.formElements = new Array();
    };
    
    this.validate = function()
    {
        var validator = new formValidator();
        var formElmts = this.getFormElements();
        
        for (var i = 0;i < formElmts.length; i++) {

            var elm = formElmts[i].elmnt;
            if(!elm && formElmts[i].id) {
                elm = document.getElementById(formElmts[i].id);
            }
            
            if(!elm) { continue; }
            
            var error = validator.check(formElmts[i],elm.value);
            
            if(error) {
                elm.focus();
                alert(error);
                return false;
            }
        }
        return true;
    };
    
    // Submit the form after completing the validation 
    this.submit = function(formId)
    {
        this.setForm(formId); //makes it easy to call the submit function without initialization from the form controllers
        if(!this.validate()) {return false;}
        coreMain.reqObj.requestToServer({
                                             method:'post',
                                             params:this.getFormSerialized()
                                         });

        return false;
    };

    this.getFormSerialized = function()
    {
        var serz = '';
        var formElmts = this.getFormElements();

        for (var i = 0;i < formElmts.length; i++) {
            var elm = formElmts[i].elmnt;
            serz += elm.name + '=' + getElementValue(elm);
            serz += i < (formElmts.length) - 1 ? '&' : '';
        }
        return serz;
    }
}


function formValidator()
{
    
    this.check = function(elmProperties,value)
    {
        var type = elmProperties.type
        var required = elmProperties.required
        var label = elmProperties.label
        var minLength = elmProperties.minLength ? elmProperties.minLength : false;
        var maxLength = elmProperties.maxLength ? elmProperties.maxLength : false;
        
        if (this.trim(value) == "") {
            if(required) {return "Fill-in " + label + " before proceeding ";}
            return false;
        }
        
        if(minLength || maxLength) {
            if(minLength & maxLength && (value.length < minLength || value.length > maxLength)) {return label + "  contain " + minLength + " to " + maxLength + " characters.";}
            if(minLength && value.length < minLength) {return label + " should be having atleast " + minLength + " characters";}
            if(maxLength && value.length > maxLength) {return label + " can have only upto " + maxLength + " characters";}
        }
        switch(type)
        {
            case 'decimal':
                error = this.validateDecimal(value);
            break;            
            case 'int':
                error = this.validateInteger(value);
            break;
            case 'email':
                error = this.validateEmail(value);
            break;
            case 'phone':
                error = this.validatePhone(value);            
            break;
            case 'date':
                error = this.validateDate(value);    
            break;
            case 'dob':
                error = this.validateDOB(value);  
            break;
            case 'ip':
                error = this.validateIP(value);    
            break;
            default:
                error = false;
            break;
        }
        if(error) {return label + '' + error;}
        return false;
    };
    
    this.trim = function (str) 
    {       
        while (str.charAt(str.length - 1)==" ")   str = str.substring(0, str.length - 1);
        while (str.charAt(0)==" ")   str = str.substring(1, str.length);
        return str;
    };
    // Number Validation -- will accept numbers and '.'
    this.validateDecimal = function  (value) 
    {               
        for (var i = 1; i < value.length; i++) {    
            var ch = value.substring(i, i + 1);                   
             if ((ch < "0" || "9" < ch) & (ch !=".")) {return(" accepts only decimal or integers ");}
        } 
        return false;
    };
    
    // Real Number Validation 
    this.validateInteger = function(value) 
    {        
        for (var i = 1; i < value.length; i++)  {    
            var ch = value.substring(i, i + 1);                   
             if (ch < "0" || "9" < ch) {return(" accepts only numbers ");}
        } 
        return false;
    };
    
    
    this.validateEmail = function (value)
    {                 
        if (value.length < 3) {return(":: please enter at least 3 characters in the \"email\" field.");}
        for (var i = 1; i < value.length; i++) {    
            var ch = value.substring(i, i + 1);   
            if ( ((ch < "a" || "z" < ch) && (ch < "A" || "Z" < ch)) && (ch < "0" || "9" < ch) && (ch != '_')&& (ch !='@')&& (ch != '.') && (ch != '-') ) {   
                    return("::some special character and blank space not allowed");
            }
         } 
               
        var error = true; 
        var theStr = new String(value);
        var index = theStr.indexOf("@");
        if (index > 0) {     
        var pindex = theStr.indexOf(".",index);
            if ((pindex > index+1) && (theStr.length > pindex+1))    error = false;
        }
         if (error) {return("::please enter a complete email address in the form: yourname@yourdomain.com");}
         return false;
    };
        
    // Phone Number validation -- accepts -,space,comma,],[,/
    this.validatePhone = function (value)
    {
       if(value.length>1 & value.length<=5) {return(" Enter valid data");}    
        for (var i = 1; i < value.length; i++) {    
            var ch = value.substring(i, i + 1); 
              
            if ((ch < "0" || "9" < ch) & (ch != '-') & (ch != ' ') & (ch !=",") & (ch != "]") & (ch != "[") & (ch != "/") & (ch != ",") ) {  
                return(" accepts only numbers , blank space & some special characters - , [ ] /");
            }
        }
        return false; 
    };
    
    this.validateDate = function (value)
    {
        var error=false;
        if(value.length <8 || value.length >10) {return (" enter proper Date value");}
        
        if(value.search("/") > -1 ) {
           var dteArray=(value.split("/"));
        }
            
        if(value.search("-")  > -1) {
           var dteArray=(value.split("-"))
        }        
        if(isNaN(dteArray[0]) || isNaN(dteArray[1]) || isNaN(dteArray[2])) {
            return (" enter proper Date value");
        }      
        return false;  
    };   
    
    this.validateDOB = function(value)
    {
        var error=this.validateDate(value);
        if(error) {return error;}

        if(value.search("/")) {var dtArray=(value.split("/"));}                
        if(value.search("-")) {var dtArray=(value.split("-"));}
        
        var currYear = d.getFullYear();
        if(dtArray[2] > currYear)  {
          error=" enter proper year of birth";
        } 
        return false;
    };
    
    this.validateIP = function(value)
    {        
        var ipVals=value.split(".")
        if(ipVals.length< 4) {return ("Enter proper IP value");}
        
        for(var i=0;i< 4;i++) {
            if(ipVals[i] >=255) {return ("Enter proper IP value");}                
        }  
        return false;      
    };   
}
