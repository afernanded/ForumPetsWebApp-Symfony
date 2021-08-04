function meGusta(id) {
    var ruta = Routing.generate('likes');
    $.ajax({
        type: 'POST',
        url: ruta,
        data: ({id:id}),
        async: true,
        dataType: "json",
        success: function (data) {
            window.location.reload();
        }
    });
}

function noMeGusta(id) {
    var ruta = Routing.generate('dislike');
    $.ajax({
        type: 'POST',
        url: ruta,
        data: ({id:id}),
        async: true,
        dataType: "json",
        success: function (data) {
            window.location.reload();
        }
    });
}