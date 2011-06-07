/**
 * Form validation and helpre functions
 */
function HelperForm()
{
    this.form;
    this.elementTags = new Array('INPUT', 'TEXTAREA', 'SELECT', 'BUTTON' );

    this.setForm = function(formId)
    {
        this.reset();
        if(!document.getElementById(formId)) {return false;}
        this.form = document.getElementById(formId);
        this.markFieldsMandatory();
        return true;
    };

    // Marks mandatory fields in a form with asteriks(*'s)
    this.markFieldsMandatory = function ()
    {
        var validator = new formValidator();
        for(var eIdx = 0; eIdx < this.form.length; eIdx++) {
            var elm = this.form.elements[eIdx];
            if(!elm.name) continue;
            if(!validator.isRequired(elm)) {continue;}
            elm.parentNode.innerHTML  += "<span class='mandatory'>*</span>";
        }
        return true;
    };

    this.reset = function()
    {
        this.form = false;
    };

    this.validate = function(formId)
    {
        if(formId) {
            var formElm = document.getElementById(formId);
        } else if(this.form) {
             var formElm = this.form;
        } else {
            return false;
        }
        
        var validator = new formValidator();
        for(var eIdx = 0; eIdx < formElm.length; eIdx++) {
            var elm = formElm.elements[eIdx];
            if(!elm.name) continue;
            
            var result = validator.check(elm);
            if(result.error) {
                elm.focus();
                alert(result.error);
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

/**
 * HTML form vlaidator object
 */
function formValidator()
{
    this.digitPattern = /^[0-9]$/; //only digits
    this.decimalPattern = /^\s*(\+|-)?\d+\s*$/; //decimal number with optional +/-
    this.intigerPattern = /^\s*(\+|-)?\d+\s*$/; //real number with optional +/-
    this.numberPattern = /^\s*\d+\s*$/;//intiger value with optional spaces
    this.emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    this.stringPattern = /^[a-zA-Z0-9._-]+$/; //string with no other special chars other thatn _/-

    this.validateDecimal = function (value) {return this.decimalPattern.test(value);};
    this.validateInt = function(value) {return this.intigerPattern.test(value);};
    this.validateDigit = function(value) {return this.digitPattern.test(value);};
    this.validateNumber = function(value) {return this.numberPattern.test(value);};
    this.validateEmail = function (value) {return this.emailPattern.test(value);};
    this.validateString = function (value) {return this.stringPattern.test(value);};
    
    this.check = function(elm)
    {
        var label = elm.title
        var value = elm.value

        if (this.trim(value) == "" && this.isRequired(elm)) { return {error: "Fill-in" +" " + label +" "}; }

        var minLength = elm.minLength >= 0 ? elm.minLength : false;
        var maxLength = elm.maxLength > 0 ? elm.maxLength : false;
        if(minLength || maxLength) {
            if(minLength & maxLength && (value.length < minLength || value.length > maxLength)) { return {error: label + " "+ "contain" +" " + minLength +" - " + maxLength +" "+ "characters."}; }
            if(minLength && value.length < minLength) { return {error: label +" "+ "should be having atleast" +" "+ minLength +" "+ "characters"}; }
            if(maxLength && value.length > maxLength) { return {error: label +" "+ "can have only upto" +" "+ maxLength +" "+ "characters"}; }
        }

        switch(this.getContentType(elm))
        {
            case 'decimal':
                error = !this.validateDecimal(value);
            break;
            case 'int':
                error = !this.validateInt(value);
            break;
            case 'digit':
                error = !this.validateDigit(value);
            break;
            case 'number':
                error = !this.validateNumber(value);
            break;
            case 'email':
                error = !this.validateEmail(value);
            break;
            default:
                error = false;
            break;
        }
        if(error) {return {error: label + ' - ' + 'enter proper value'} };
        return true;
    };

    this.trim = function (str)
    {
        while (str.charAt(str.length - 1)==" ")   str = str.substring(0, str.length - 1);
        while (str.charAt(0)==" ")   str = str.substring(1, str.length);
        return str;
    };

    this.isRequired = function(elm)
    {
        var clNames = elm.className.split(' ');
        for(var cIdx in clNames) {
            if(clNames[cIdx].indexOf('required') > -1) {
                return true;
            }
        }
        return false;
    };

    this.getContentType = function(elm)
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
}
