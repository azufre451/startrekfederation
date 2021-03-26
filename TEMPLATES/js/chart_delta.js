function lookerUp() {
    jQuery.ajax({
        url: "ajax_planetSearch.php?mode=3",
        data: {
            term: jQuery("#systemSearcher").val()
        },
        type: "POST",
        dataType: "json",
        success: function(e) {
            map.setView(map.unproject([e[0], e[1]], 6), 6);
            jQuery("#searcher").fadeOut()
        }
    })
}
function setPosition(e, t) {
    map.setView(map.unproject([e, t], 6), 6)
}
function initiate(e, t) {
    var n = 3;
    var r = 6;
    map = L.map("map", {
        maxZoom: r,
        minZoom: n,
        crs: L.CRS.Simple,
        maxBounds: [[-156, 0], [0, 157]]
    }).setView([0, 0], 6);
    L.tileLayer("TEMPLATES/img/charts/" + jQuery('#map').attr('data-imagery') + "/{z}/{x}/{y}.png", {
        minZoom: n,
        maxZoom: r,
        attribution: "Star Trek: Federation",
        noWrap: true
    }).addTo(map);
    var i = L.popup();
    map.on("dblclick", function(e) {
    	coor=map.project(e.latlng, 6);
    	
        i.setLatLng(e.latlng).setContent("<p style='font-size:16px;'>Solo un punto vuoto nello spazio... <br/>Coordinate: X=" + coor.x +' Y=' + coor.y  + "</p>").openOn(map)
    });
    if (e > 0 && t > 0) {
        map.setView(map.unproject([e, t]), 6);
        var s = L.marker(map.unproject([e, t]), {
            title: "La tua posizione"
        }).addTo(map)
    } else {
        map.setView(map.unproject([3950, 3940]), 6)
    }
}
function doE() {
    map.panTo(map.unproject([3300, 3093]), 6)
}
function getMessage(e) {
    var t;
    var n = jQuery.ajax({
        url: "ajax_planetSearch.php?mode=2",
        data: {
            term: e,
            subCharts: jQuery('#map').attr('data-sector')
        },
        type: "POST",
        dataType: "json",
        async: false,
        success: function(e) {
            t = e
        },
        timeout: 2500
    });
    return t
}
function addMarker(e, t, tp) {


    ccl='#3F3'; cclborder='green'; ccl='#3F3';
    var n = L.circleMarker(map.unproject([e, t]), {
        radius: 6,
        color: cclborder,
        fillColor: ccl,
        fillOpacity: 1,
        weight: 4
    }).addTo(map);
    n.on("click", function() {
        coordinatesContent = getMessage(e + ";" + t);
        if (coordinatesContent.length == 1) {
            if (coordinatesContent[0]["N0"] == "Pianeta")
                n.bindPopup('<div style="width: 450px;"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/' + coordinatesContent[0]["N3"] + '\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/' + coordinatesContent[0]["N2"] + '\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne">Nome</td><td class="bdr">' + coordinatesContent[0]["N1"] + " " + (coordinatesContent[0]["N4"] != "" ? '<span class="iGray">(' + coordinatesContent[0]["N4"] + ")</span>" : "") + " " + (coordinatesContent[0]["N13"] != 0 ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\'' + coordinatesContent[0]["N13"] + '\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : "") + '</td></tr><tr><td class="borderOne">Allineamento</td><td class="bdr">' + coordinatesContent[0]["N5"] + '</td></tr><tr><td class="borderOne">Settore</td><td class="bdr">' + coordinatesContent[0]["N6"] + '</td></tr><tr><td class="borderOne">Specie Dominante</td><td class="bdr">' + coordinatesContent[0]["N12"] + '</td></tr><tr><td class="borderOne">Popolazione</td><td class="bdr">' + coordinatesContent[0]["N8"] + '</td></tr><tr><td class="borderOne">Tempo Rotazione</td><td class="bdr">' + coordinatesContent[0]["N9"] + ' ore</td></tr><tr><td class="borderOne">Dettagli</td><td class="bdr">' + coordinatesContent[0]["N7"] + " </td></tr></table></div></div>");
            else
                n.bindPopup('<div style="width: 450px;"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/' + coordinatesContent[0]["N3"] + '\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/' + coordinatesContent[0]["N2"] + '\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne">Nome / Registro</td><td class="bdr">' + coordinatesContent[0]["N1"] + " " + (coordinatesContent[0]["N13"] != 0 ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\'' + coordinatesContent[0]["N13"] + '\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : "") + '</td></tr><tr><td class="borderOne">Classe</td><td class="bdr">' + coordinatesContent[0]["N12"] + '</td></tr><tr><td class="borderOne">Affiliazione</td><td class="bdr">' + coordinatesContent[0]["N5"] + '</td></tr><tr><td class="borderOne">Uff. Comandante</td><td class="bdr"><img src="TEMPLATES/img/ranks/' + coordinatesContent[0]["N10b"] + '.png"></img> <a class="iLink" href="javascript:schedaPOpen(' + coordinatesContent[0]["N10e"] + ');"> ' + coordinatesContent[0]["N10c"] + " " + coordinatesContent[0]["N10d"] + "</a></td></tr>" + (coordinatesContent[0]["N11"] != "" ? '<tr><td class="borderOne">Motto</td><td class="bdr">' + coordinatesContent[0]["N11"] + "</td></tr>" : "") + '<tr><td class="borderOne">Equipaggio</td><td class="bdr">' + coordinatesContent[0]["N8"] + '</td></tr><tr><td class="borderOne">Dettagli</td><td class="bdr">' + coordinatesContent[0]["N7"] + " </td></tr></table></div></div>")
        } else {
            arelem = "";
            i = 0;
            arelemIndex = '<p class="title">Unità alla Locazione:</p><br/>';
            $(coordinatesContent).each(function(e, t) {
                if (t["N0"] == "Pianeta") {
                    arelem += '<div style="display:none;" id="dlem_' + i + '"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/' + t["N3"] + '\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/' + t["N2"] + '\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne">Nome</td><td class="bdr">' + t["N1"] + " " + (t["N4"] != "" ? '<span class="iGray">(' + t["N4"] + ")</span>" : "") + " " + (t["N13"] != 0 ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\'' + t["N13"] + '\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : "") + '</td></tr><tr><td class="borderOne">Allineamento</td><td class="bdr">' + t["N5"] + '</td></tr><tr><td class="borderOne">Settore</td><td class="bdr">' + t["N6"] + '</td></tr><tr><td class="borderOne">Specie Dominante</td><td class="bdr">' + t["N12"] + '</td></tr><tr><td class="borderOne">Popolazione</td><td class="bdr">' + t["N8"] + '</td></tr><tr><td class="borderOne">Tempo Rotazione</td><td class="bdr">' + t["N9"] + ' ore</td></tr><tr><td class="borderOne">Dettagli</td><td class="bdr">' + t["N7"] + " </td></tr></table></div></div>"
                } else {
                    arelem += '<div style="display:none;" id="dlem_' + i + '"><div class="leftMarkers"><div style="background-image:url(\'TEMPLATES/img/logo/' + t["N3"] + '\');" class="backgrounder"></div><div style="background-image:url(\'TEMPLATES/img/logo/' + t["N2"] + '\');" class="backgrounder"></div></div><div class="rightMarkers"><table style="width:100%;"><tr><td class="borderOne">Nome / Registro</td><td class="bdr">' + t["N1"] + " " + (t["N13"] != 0 ? ' <p class="iColor"><a href="javascript:void(0);" onclick="dbOpenToTopic(\'' + t["N13"] + '\')"><img src="TEMPLATES/img/interface/personnelInterface/external_link.png"></img></a></p>' : "") + '</td></tr><tr><td class="borderOne">Classe</td><td class="bdr">' + t["N12"] + '</td></tr><tr><td class="borderOne">Affiliazione</td><td class="bdr">' + t["N5"] + '</td></tr><tr><td class="borderOne">Uff. Comandante</td><td class="bdr"><img src="TEMPLATES/img/ranks/' + t["N10b"] + '.png"></img> <a class="iLink" href="javascript:schedaPOpen(' + t["N10e"] + ');"> ' + t["N10c"] + " " + t["N10d"] + "</a></td></tr>" + (t["N11"] != "" ? '<tr><td class="borderOne">Motto</td><td class="bdr">' + t["N11"] + "</td></tr>" : "") + '<tr><td class="borderOne">Equipaggio</td><td class="bdr">' + t["N8"] + '</td><tr><td class="borderOne">Dettagli</td><td class="bdr">' + t["N7"] + " </td></tr></table></div></div>"
                }
                arelemIndex = arelemIndex + '<div class="iElement" onclick="deShowMultiple(\'#dlem_' + i + "');\" style=\"background-image:url('TEMPLATES/img/logo/" + t["N3"] + '\');" title="' + t["N1"] + '"></div>';
                i++
            });
            n.bindPopup('<div style="width: 430px;"><div id="arelmDiv">' + arelemIndex + "</div>" + arelem + "</div>")
        }
    })
}
function deShowMultiple(e) {
    jQuery("#arelmDiv").fadeOut(300, function() {
        jQuery(e).fadeIn(150)
    })
}
function addMarkersFromJSon(e) {
    $.each(jQuery.parseJSON(e), function(e, t) {
        addMarker(t[0], t[1], t[2])
    })
}
var map;
jQuery(function() {
    jQuery("#introb").button();
    jQuery("#systemSearcher").autocomplete({
        source: "ajax_planetSearch.php?mode=1",
        minLength: 2
    });

    jQuery('#map').css('height',jQuery( window ).height()-40);
    initiate( jQuery('#map').attr('data-xpos') , jQuery('#map').attr('data-ypos'));
});

jQuery(window).resize(function() {
	jQuery('#map').css('height',jQuery( window ).height()-40);
	
});