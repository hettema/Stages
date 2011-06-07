function popupCalendar(elmId)
{
    var dateSelected    = getElementValue(elmId);
        window.open(server['SERVER_URI']+'/template/calendar.php'+'?dateSelected=' + dateSelected+ '&dtFieldId=' + elmId,'Calendar','titlebar=no,toolbar=no,width=200,height=194,status=no,resizable=no,top=200,left=200,dependent=yes,alwaysRaised=yes');
}

function getUrl(path)
{
    path = !path ? '' : path;
    return window.location.protocol +"//"+ window.location.host +"/"+ path;
}

function getElementValue(elm)
{
    if(typeof(elm) == 'string') {
        if(!document.getElementById(elm)) return false;
        elm  = document.getElementById(elm);
    }
    switch(elm.tagName.toLowerCase())
    {
        case 'input':
        case 'textarea':
            if(elm.type && (elm.type=='checkbox' || elm.type=='radio')) {
                return elm.checked;
            }
            return elm.value;
        break;
                
        case 'select':
            var value = elm.value;
            if(elm.getAttribute('multiple')) {
                var arrVals = new Array();
                for(var opIdx = 0; opIdx < elm.options.length; opIdx++) {
                    if(elm.options[opIdx].selected) {
                        if(elm.options[opIdx].value == '') continue;
                        arrVals[arrVals.length] = elm.options[opIdx].value;
                    }
                }
                value = arrVals.join(',');
            }
            return value;
        break;
        
        default:        
            //return elm.value;
        break;
        
    }
    return false;
}

function isArray(obj) {
   if (obj.constructor.toString().indexOf("Array") == -1) return false;
   else return true;
}

function inArray(arrayInput,str)
{
    for(var arrKey in arrayInput) {
        if(arrayInput[arrKey] == str) return true; 
    }
    return false;
}
/////////////////////////
function initXmlObj()
{    
    if (window.ActiveXObject) {  
       var xmlObj  = new ActiveXObject("Microsoft.XMLDOM");
               xmlObj.loadXML("<doc></doc>");
    } else if (document.implementation && document.implementation.createDocument) {
        var xmlObj  = document.implementation.createDocument("","doc",null);
    }
    return xmlObj;
}

function getNodeValue(pNode,childName,noValue)
{
    if(pNode.childNodes.length < 1) return noValue;
    if(!childName) return pNode.firstChild.data;
    
    if(pNode.getElementsByTagName(childName).length  < 1) return noValue;
    if(!pNode.getElementsByTagName(childName)[0].firstChild) return noValue;
    if(!pNode.getElementsByTagName(childName)[0].firstChild.data) return noValue;
    return pNode.getElementsByTagName(childName)[0].firstChild.data;
}

function toggleHover(el, over)
{
    var clName = el.className;
    if (over) {
        if(clName.length > 0) { clName += ' '; }
        clName = clName + 'over';
    }
    else {
       if(clName == 'over') { clName = ''; }
        else { 
            clName = clName.replace(' over','');
        }
    }
    el.className = clName;
    
}

function displayXML(xmlObj)
{
    var xmlString;
    if(window.ActiveXObject) {
        xmlString        = xmlObj.xml;
    } else {
        var serializer     = new XMLSerializer();
        xmlString        = serializer.serializeToString(xmlObj);
    }
    alert(xmlString);
}

function drawElement(elmInfo)
{
    if(!elmInfo.tag) return false;
    var elm = document.createElement(elmInfo.tag);

    for(var field in elmInfo) {
        if(field == 'tag') continue;
        if(field == 'data') {
            elm.innerHTML = elmInfo[field];
            continue;
        }
        if(field == 'css_class') {
            elm.setAttribute('class',elmInfo[field]);
            continue;
        }
        //Assign the attribute and values
        elm.setAttribute(field,elmInfo[field]);
    }
    return elm;
}

function drawSelectElement(elmInfo)
{
    elmInfo = elmInfo ? elmInfo : {};
    elmInfo.tag = 'select';
    elmInfo.css_class = 'select '+ elmInfo.css_class;
    var inputElm = drawElement(elmInfo);
    drawChildElement(inputElm, {tag: 'option', data: 'Select '+elmInfo.title, value: ''});
    var options = elmInfo.options ? elmInfo.options : new Array();
    for(var oIdx in options.list) {
        if(!options.list[oIdx]) { continue; }
        var key = options.useValueAsKey ? options.list[oIdx] : oIdx;
        var optionInfo = {tag: 'option', value: key, data: options.list[oIdx]};
        var optionELm = drawChildElement(inputElm, optionInfo);
            optionELm.selected = (key == options.selected) ? 'selected' : false;
    }
    return inputElm;
}

function drawChildElement(parent, elmInfo)
{
    var elm = drawElement(elmInfo);
    parent.appendChild(elm);
    return elm;
}

function removeElement(elm)
{
    return elm.parentNode.removeChild(elm);
}

function showHideElement(elmId)
{
    if(!document.getElementById(elmId)) return false;
    var elm  = document.getElementById(elmId);
    return elm.style.display = elm.style.display == 'none' ? '' : 'none';
}
