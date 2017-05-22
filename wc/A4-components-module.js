//ASSIGNMENT 4 WEB COMPONENTS
//MODULE FILE
//IN THIS FILE YOU WILL CREATE YOUR CUSTOM MODULE FOR YOUR REQUIRED FUNCTIONALITY AND EXPOSE IT THROUGH A 'PUBLICLY' ACCESSIBLE METHOD.
var humberClock_Module = (function () {

    var getTime = function () 
    {
        var time = new Date();
        return addZeros(time.getHours()) + " : " + addZeros(time.getMinutes()) + " : " + addZeros(time.getSeconds());
    }

    function addZeros (timeItem)
    {
    	if(timeItem<10) 
    		return "0"+ timeItem;
    	else 
    		return timeItem; 
    };

    return {getTime: getTime}
})();