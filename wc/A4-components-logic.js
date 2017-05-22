//ASSIGNMENT 4 WEB COMPONENTS
//LOGIC FILE
//IN THIS FILE YOU WILL SIMPLY GRAB YOUR CUSTOM ELEMENT AND ATTACH YOUR METHOD FROM YOUR MODULE TO IT.
window.onload=function (){

	var timerContainer = document.querySelector("humber-clock span");
	updateTime();
	var timerInv = setInterval(updateTime, 1000);

	function updateTime ()
	{
	    timerContainer.innerHTML = humberClock_Module.getTime();
	}	
}
