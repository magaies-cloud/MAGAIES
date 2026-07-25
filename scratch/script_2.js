
document.addEventListener("DOMContentLoaded", function() {
    // Default Dates: From the 1st of the current month to Today
    const dFrom = document.getElementById('filter-from');
    const dTo = document.getElementById('filter-to');
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const formatDate = (d) => {
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [d.getFullYear(), month, day].join('-');
    };
    
    if (dFrom && !dFrom.value) dFrom.value = formatDate(firstDay);
    if (dTo && !dTo.value) dTo.value = formatDate(today);
});
