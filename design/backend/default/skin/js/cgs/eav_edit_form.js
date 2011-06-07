function EavEditForm()
{
    this.entityAttributeValues = false;
    this.inputContainer = false;// table container wraper for all input fields. wil be loaded prior to drawing all the attributes
    this.helperForm = false;
    this.form;
    
    this.setEntityAttributeValues = function(attributeValues)
    {
          this.entityAttributeValues = attributeValues;
    };
    
    this.beforeLoadingEav = function(containerId, formId)
    {
        var container = document.getElementById(containerId);
        this.helperForm = new HelperForm();
        //reset the form object;
        this.helperForm.reset();
        
        formId = !formId || formId == 'undefined' ? 'eav-edit-form' : formId;
        this.helperForm.setForm(formId);
        
        if(!document.getElementById(formId)) {
            container.innerHTML = '';
            this.form = drawChildElement(container, {tag:'form', id : 'eav-edit-form', name : 'eav-edit-form'});
        } else {
            this.form = document.getElementById(formId);
        }
        
        this.inputContainer = drawChildElement(this.form, {tag: 'table', cellpadding:0, cellspacing:0, border:0});
        
        if(this.entityAttributeValues && getNodeValue(this.entityAttributeValues,'entity_id')) {
             drawChildElement(this.form, {tag:'input', id : 'entity_id', name : 'entity_id', type: 'hidden', value:getNodeValue(this.entityAttributeValues,'entity_id')});
        }
        return this;
    };

    this.drawAttributes = function(attributesNode)
    {        
        for(var aIdx = 0; aIdx < attributesNode.length; aIdx++) {
            this.drawAttribute(attributesNode[aIdx]);
        }
    };

    this.drawAttribute = function(attributeNode)
    {
        if(!this.inputContainer) {alert('container wrap not found');return true;}
        var attributeId = attributeNode.getAttribute('id');
        var attributeLabel = attributeNode.firstChild.data;

        var inputElm = this.getAttributeInputElement(attributeNode);
        if(!inputElm) return true;
        
        var tr = drawChildElement(this.inputContainer, {tag:'tr'});
        drawChildElement(tr, {tag:'th', id : 'attribute-field-' + attributeId, css_class : 'attribute-field', data:attributeLabel});
        var td = drawChildElement(tr, {tag:'td', id : 'attribute-input-' + attributeId});

        if(attributeNode.getElementsByTagName('value_prepend')[0]) {
            var strPrepend = attributeNode.getElementsByTagName('value_prepend')[0].firstChild.data;
            if(strPrepend && strPrepend != '') {                
                drawChildElement(td, {tag:'span', css_class : 'value-prepend', data:strPrepend});
            }
        }
        td.appendChild(inputElm);

        return true;
    };

    this.getAttributeInputElement = function(attributeNode)
    {
        var infoNode = attributeNode.getElementsByTagName('attribute_info')[0];//this.getNodeAsObject();
        var valuesNode = attributeNode.getElementsByTagName('option_values')[0];
        var isEditable = getNodeValue(infoNode,'is_editable');
        if(isEditable != 1) { return false; }
        
        var attrCode  = getNodeValue(infoNode,'attribute_code');
        var defaultValue = getNodeValue(infoNode,'default_value') ? getNodeValue(infoNode,'default_value') : '';
        if(this.entityAttributeValues && getNodeValue(this.entityAttributeValues, attrCode)) {
            defaultValue = getNodeValue(this.entityAttributeValues,attrCode);
        }
        var validate = false;

        var inputType = getNodeValue(infoNode,'frontend_input');
        switch(inputType)
        {
            case 'text':
                var inputElm = drawElement( {tag: 'input', type: 'text', value: defaultValue} );
            break;

            case 'textarea':
                validate = true;
                var inputElm = drawElement( {tag: 'textarea', cols: 15, rows: 2, data: defaultValue} );
            break;

            case 'select':
            case 'multiselect':
                if(inputType == 'select')  { var inputElm = drawElement( {tag: 'select'} );}
                else                       { var inputElm = drawElement( {tag: 'select', multiple: 'multiple'} );}
                var optionElm = drawElement( {tag: 'option', value: ''} );
                inputElm.appendChild(optionElm);
                if(!valuesNode || !valuesNode.childNodes) { break; }
                
                if(valuesNode.childNodes.length > 0) {
                    for(vIdx = 0; vIdx < valuesNode.childNodes.length; vIdx++) {
                        var valueNode =valuesNode.childNodes[vIdx];
                             optionElm = drawElement( {tag: 'option', value: valueNode.getAttribute('id'), data: valueNode.firstChild.data} );

                        if(defaultValue) {
                            var chkValues =  defaultValue.split(',');
                            if(inArray(chkValues, valueNode.getAttribute('id'))) {
                            optionElm.setAttribute('selected','selected');
                            }
                        }
                        inputElm.appendChild(optionElm);
                    }
                }
            break;

            case 'boolean':
                var inputElm = drawElement( {tag: 'select'} );
                     for(var bool=0; bool < 2; bool++) {
                         var optionData = bool == 1 ? 'Yes' : 'No';
                         optionElm = drawElement( {tag: 'option', value: bool, data: optionData} );
                         if(defaultValue && defaultValue == bool) {
                             optionElm.setAttribute('selected','selected');
                         }
                        inputElm.appendChild(optionElm);
                     }
            break;

            case 'date':
                var inputElm = drawElement( {tag: 'input', type: 'text', value: defaultValue.substr(0,10), css_class: 'cal-input', onclick: 'Calendar.popup(this);'} );
            break;

            case 'price':
                validate = true;
                var inputElm = drawElement( {tag: 'input', type: 'text', value: defaultValue} );
            break;

            case 'media_image':
                var inputElm = drawElement( {tag: 'input', type: 'text', value: defaultValue} );
            break;

            case 'gallery':
                var inputElm = drawElement( {tag: 'input', type: 'text', value: defaultValue} );
            break;

            default:
                //var inputElm = drawElement( {tag: 'input', type: 'text', value: defaultValue} );
            break;
        }
        if(!inputElm) return false;

        
        //set the input elements css class
        inputElm.setAttribute('class', inputType);
        var elmId = 'input_'+ attrCode
        
        inputElm.setAttribute('name', attrCode);
        inputElm.setAttribute('id', elmId);
        
        if(getNodeValue(infoNode,'is_required')) {
            inputElm.className = inputElm.className + ' required';
            if(inputType == 'textarea' && getNodeValue(infoNode,'backend_type') == 'varchar') {
            this.helperForm.setFormElemnt( {id: elmId, required: 1, label: getNodeValue(infoNode,'frontend_label'), type: getNodeValue(infoNode,'backend_type'), maxLength:254} );
            }
            this.helperForm.setFormElemnt( {id: elmId, required: 1, label: getNodeValue(infoNode,'frontend_label'), type: getNodeValue(infoNode,'backend_type')} );
        } else if (validate) {
            if(inputType == 'textarea' && getNodeValue(infoNode,'backend_type') == 'varchar') {
            this.helperForm.setFormElemnt( {id: elmId, required: 0, label: getNodeValue(infoNode,'frontend_label'), type: getNodeValue(infoNode,'backend_type'), maxLength:254} );
            }
            this.helperForm.setFormElemnt( {id: elmId, required: 0, label: getNodeValue(infoNode,'frontend_label'), type: getNodeValue(infoNode,'backend_type')} );
        }

        return inputElm;
    };

    this.afterLoadingEav = function()
    {
        if(!this.inputContainer) {alert('container wrap not found');return true;}
        
        var tr = drawChildElement(this.inputContainer, {tag:'tr'});
        
        var btnWrap = drawChildElement(tr, {tag:'td', colspan:2});
        drawChildElement(btnWrap, {tag:'input', id : 'button-back', css_class : 'submit', type: 'button', value: 'back', onclick: 'eavEditObj.back()'});
        //drawChildElement(btnWrap, {tag:'input', id : 'button-reset', css_class : 'submit', type: 'button', value: 'Reset', onclick: 'eavEditObj.reset()'});
        drawChildElement(btnWrap, {tag:'input', id : 'button-save', css_class : 'submit', type: 'button', value: 'Save',  onclick: 'eavEditObj.submitForm()'});
        //drawChildElement(btnWrap, {tag:'input', id : 'button-save-edit', css_class : 'submit', type: 'button', value: 'Save & Edit',  onclick: 'eavEditObj.submitForm("edit")'});
        //drawChildElement(btnWrap, {tag:'input', id : 'button-save-new', css_class : 'submit', type: 'button', value: 'Save As New', onclick: 'eavEditObj.submitAsNewEntity()'});

        return this;
    };

    this.beforeSubmit = function()
    {
        return  this.helperForm.validate();
    };

    this.getForm = function()
    {
        return this.form;
    }
}