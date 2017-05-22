
window.onload= function () {
   document.getElementById('retry').onclick= function(){
    var el=document.getElementById('finish');
    el.className="";
    setTimeout(function(){el.className="gameover";},10)
    var tel=document.querySelector('.timer span');
    tel.className="";
    setTimeout(function(){tel.className="timer-animation";},10)
    document.getElementById('circle1').checked =false;
    document.getElementById('circle2').checked =false;
    document.getElementById('circle3').checked =false;
    document.getElementById('circle4').checked =false;
    document.getElementById('circle5').checked =false;
    document.getElementById('circle6').checked =false;
    };
}