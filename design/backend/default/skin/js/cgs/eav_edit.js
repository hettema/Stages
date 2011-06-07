function EavEdit(entityTypeId, contentWraperId, submitUrl, formId)
{
    this.entityTypeId = entityTypeId;
    this.contentWraper = contentWraperId;
    this.submitUrl = submitUrl;
    this.checkUniqueUrl = getUrl('backend/eav/check_unique');
    this.formId = formId;
    this.eavForm = false;
    this.entityTypeId;
    this.entityId;
    this.eav_attributes;
    this.eav_entity;
    
    var self = this;
    
    this.loadEavInfo = function (reqUrl, entityId)
    {
        if(entityId) {
            sendData = "entityId="+entityId
        } else {
            sendData = '';
        }
        $.ajax({url: reqUrl , success: this.initEavForm, dataType: 'xml', data: sendData});
        return true;
    };

    self.initEavForm = function(xmlInfo)
    {
        self.eavInfo = xmlInfo;
        self.drawEditInterface();
    };

    this.drawEditInterface = function()
    {
        if(!this.eavInfo.getElementsByTagName('eav_attributes')) {
            return null;
        }

        var eav_attributes = this.eavInfo.getElementsByTagName('eav_attributes')[0];
        if(!eav_attributes) {alert('unable to load the attribute info');return false;}
        this.entityTypeId = eav_attributes.getAttribute('entity_type_id');
        this.eav_attributes = eav_attributes.childNodes;

        if(this.eavInfo.getElementsByTagName('entity_info')[0]) {
            var entityInfo = this.eavInfo.getElementsByTagName('entity_info')[0];
            this.eav_entity = entityInfo.childNodes[0];
        }
        this.eavForm = new EavEditForm();
        this.eavForm.setEntityAttributeValues(this.eav_entity);
        this.eavForm.beforeLoadingEav(this.contentWraper, this.formId);
        this.eavForm.drawAttributes(this.eav_attributes);
        this.eavForm.afterLoadingEav();
        
        return this;
    };

    this.getAttributeInfo = function(attributeCode)
    {
         for(var aIdx = 0; aIdx < this.eav_attributes.length; aIdx++) {
            var attrInfoNode = this.eav_attributes[aIdx].getElementsByTagName('attribute_info')[0];
            if(getNodeValue(attrInfoNode,'attribute_code') == attributeCode) {
                return attrInfoNode;
            }
         }
    };

    this.reset = function()
    {
        this.drawEditInterface();
        this.formValidated = false;
    };

    /************* Submit form function *****************/
    this.submitAsNewProduct = function()
    {
        if(document.getElementById('entity_id')) {
           document.getElementById('entity_id').value = false;
        }
        this.submitForm();
    };
    
    this.beforeSubmitForm = function()
    {
        if(!this.eavForm.beforeSubmit()) return false;
        return true;
    };

    this.submitForm = function(submitType)
    {
        if(!this.beforeSubmitForm()) return false;
        this.checkUnique(submitType);

    };

    this.checkUnique = function()
    {
        var form = this.eavForm.getForm();
        var xmlObj = initXmlObj();
        var dataNode = xmlObj.createElement('data');
        var eavUniqueNode = xmlObj.createElement('eav_check_unique');
            dataNode.appendChild(eavUniqueNode);
            eavUniqueNode.setAttribute('entity_type_id', this.entityTypeId);
            
        if(getElementValue('entity_id')) {
           eavUniqueNode.setAttribute('entity_id',getElementValue('entity_id'));
        }
        
        var hasUniqueAttributes = false;
        for(var aIdx = 0; aIdx < this.eav_attributes.length; aIdx++) {
            var attrInfoNode = this.eav_attributes[aIdx].getElementsByTagName('attribute_info')[0];
            if(getNodeValue(attrInfoNode,'is_unique') != 1) {continue;}

            hasUniqueAttributes = true;
            var attrCode = getNodeValue(attrInfoNode,'attribute_code');

            var attrValueNode = xmlObj.createElement(attrCode);
                attrValueNode.setAttribute('attribute_id', getNodeValue(attrInfoNode,'attribute_id'));
                attrValueNode.setAttribute('backend_type', getNodeValue(attrInfoNode,'backend_type'));
            var valueNode = xmlObj.createTextNode(getElementValue('input_'+attrCode));
                attrValueNode.appendChild(valueNode);
                
            eavUniqueNode.appendChild(attrValueNode);
        }

        if(!hasUniqueAttributes) {
            this.submitFormSuccess();
        } else {
            var postData = {};
            postData.check_data = this.getXmlSerialized(dataNode);

            $.ajax({type: 'post', url: this.checkUniqueUrl, data: postData, success: this.afterCheckUnique, dataType: 'xml'});

        }        
    };

    this.afterCheckUnique = function(xmlInfo)
    {
        var chkResponse = xmlInfo.getElementsByTagName('check_unique_response')[0];
        var errors  = chkResponse.getAttribute('errors');

        if(errors && errors != 0) {

            var str = 'Error!! conflict with existing value \n';
            for(var cIdx = 0; cIdx < chkResponse.childNodes.length; cIdx++) {
                var childNode = chkResponse.childNodes[cIdx];
                var attrInfoNode = self.getAttributeInfo(childNode.nodeName);
                var attrCode = getNodeValue(attrInfoNode,'attribute_code');
                document.getElementById('input_'+ attrCode).focus();
                str += '\n'+ getNodeValue(attrInfoNode,'frontend_label') +': '+ getNodeValue(childNode);
            }
            alert(str);
        } else {
            self.submitFormSuccess();
        }
        return true;
    };

    this.submitFormSuccess = function(submitAfter)
    {
        var form = this.eavForm.getForm();
        var xmlObj = initXmlObj();
        var entitySubmitNode = xmlObj.createElement('eav-submit');
           //entitySubmitNode.appendChild(this.getUniqueAttributes(xmlObj));

        var entityNode = xmlObj.createElement('entity');
           entitySubmitNode.appendChild(entityNode);

        if(getElementValue('entity_id')) {
           entityNode.setAttribute('entity_id',getElementValue('entity_id'));
        }

        var addedAttributes = new Array();
        for(var aIdx = 0; aIdx < this.eav_attributes.length; aIdx++) {
            var attributeNode = this.eav_attributes[aIdx];
            var attrInfoNode = attributeNode.getElementsByTagName('attribute_info')[0];
            var attrValuesNode = attributeNode.getElementsByTagName('option_values')[0];
            var isEditable = getNodeValue(attrInfoNode,'is_editable');
            var attrCode = getNodeValue(attrInfoNode,'attribute_code');
            
            addedAttributes[addedAttributes.length] = attrCode;

            if(isEditable != 1) {continue;}

            var attrValueNode = xmlObj.createElement(attrCode);
                attrValueNode.setAttribute('attribute_id', getNodeValue(attrInfoNode,'attribute_id'));
                attrValueNode.setAttribute('backend_type', getNodeValue(attrInfoNode,'backend_type'));
                attrValueNode.setAttribute('frontend_input', getNodeValue(attrInfoNode,'frontend_input'));
                attrValueNode.setAttribute('is_searchable', getNodeValue(attrInfoNode,'is_searchable'));
            var valueNode = xmlObj.createTextNode(getElementValue('input_'+attrCode));
                attrValueNode.appendChild(valueNode);
            entityNode.appendChild(attrValueNode);
        }

        // add the extra form elements(other than eav attributed defined) to the xml object
        for ( i = 0; i < form.elements.length; i++ ) {
            var elm = form.elements[i];
            attrCode = elm.name;
            var value = getElementValue(form.elements[i]);
            if(!attrCode || attrCode == '') {continue;}
            if(!value) {continue;}
            if(inArray(addedAttributes, attrCode)) {continue;}

            attrValueNode = xmlObj.createElement(attrCode);
            valueNode = xmlObj.createTextNode(value);
            attrValueNode.appendChild(valueNode);
            entityNode.appendChild(attrValueNode);
        }
        
        var postData = {};
            postData.submit_after = submitAfter && submitAfter != 'undefined' ? submitAfter : '';
            postData.eav_data = this.getXmlSerialized(entitySubmitNode);
            
        $.ajax({type: 'post', url: this.submitUrl, data: postData, success: this.afterSubmitEavForm, dataType: 'xml'});

    };

    this.afterSubmitEavForm = function(xmlInfo)
    {
        if(xmlInfo.getElementsByTagName('redirect')) {
            window.location = getNodeValue(xmlInfo.getElementsByTagName('redirect')[0]);
        } else {
            self.eavInfo = xmlInfo;
            self.drawEditInterface();
        }
    }

    this.getUniqueAttributes = function(xmlObj)
    {
        return false;
        if(!this.eav_attributes.getElementsByTagName('isUnique')) return true;

        var uniqueAttributes = xmlObj.createElement('unique_attributes');

        uniqAttrs = this.getAttributeSetNode().getElementsByTagName('isUnique');
        for(var uIdx = 0; uIdx < uniqAttrs.length; uIdx++) {
            var attrCode = getNodeValue(uniqAttrs[uIdx].parentNode,'attributeCode');
            var attributeNode = xmlObj.createElement(attrCode);
                 attributeNode.setAttribute('attributeId', getNodeValue(uniqAttrs[uIdx].parentNode,'attributeId'));
                 attributeNode.setAttribute('backendType', getNodeValue(uniqAttrs[uIdx].parentNode,'backendType'));
                 attributeNode.appendChild(xmlObj.createTextNode(getNodeValue(uniqAttrs[uIdx].parentNode,'frontendLabel')));
                 uniqueAttributes.appendChild(attributeNode);
        }
         return uniqueAttributes;
    };

    this.getXmlSerialized = function(xmlObj)
    {
        xmlString = '<?xml version="1.0" encoding="UTF-8" ?>';
        if(window.ActiveXObject) {
            xmlString        += xmlObj.xml;
        } else {
            var serializer     = new XMLSerializer();
            xmlString        += serializer.serializeToString(xmlObj);
        }
        return xmlString;
    };
}