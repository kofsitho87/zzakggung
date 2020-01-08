
function getYMD(date){
    var newDate = new Date(date);
    var year = newDate.getFullYear(); 
    var month = newDate.getMonth() + 1;
    var date = newDate.getDate();

    if (month.toString().length < 2) month = '0' + month;
    if (date.toString().length < 2) date = '0' + date;
    

    //return year + '-' + mon + '-' + date;
    return [year, month, date].join('-');;
}
$(function(){
    var sdate = $('#sdate');
    var edate = $('#edate');

    $('.date').on('click', function(){
        var _sdate, _edate;

        var todayDate = new Date().getTime(); 
        
        if( $(this).hasClass('reset') ){
            _sdate = '2018-12-01';
            _edate = getYMD(todayDate);

        }else if( $(this).hasClass('today') ){
            _sdate = getYMD(todayDate);
            _edate = _sdate;
        }else if( $(this).hasClass('yesterday') ){
            var date = new Date().getTime() - (86400 * 1000);
            _sdate = getYMD(date);
            _edate = _sdate;

        }else if( $(this).hasClass('week') ){
            var sWeekDay = new Date().getDay();
            var date = new Date().getTime() - ((86400 * sWeekDay) * 1000);
            
            _sdate = getYMD(date);
            _edate = getYMD(todayDate);

        }else if( $(this).hasClass('month') ){
            var sMonthDay = new Date().getDate() - 1;
            var date = new Date().getTime() - ((86400 * sMonthDay) * 1000);
            
            _sdate = getYMD(date);
            _edate = getYMD(todayDate);
        }

        sdate.val(_sdate);
        edate.val(_edate);
    })
});