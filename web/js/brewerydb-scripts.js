/**
 * Created by lucio on 25/09/17.
 */


/**
 *  Get Page Data
 */

function updateDataSearch(atual_page = 1)
{

    var data_all     = {};
    var search       = $('#search').val();
    var _csrf_token  = $('#token').val();
    var url_Backend  = "http://" + window.location.host;

    var serializedData = { data : {
        search_text: search,
        _csrf_token: _csrf_token
      }
    }

    $.ajax({

        type:'POST',
        data: serializedData,
        dataType: 'json',
        url: url_Backend + '/search?p=' + atual_page

    }).done(function(data){

        data_all = data;
        insertResultPage(data_all);  // Updating listing data
        insertPagination(data.currentPage, data.numberOfPages, data.totalResults);

    }).fail(function() {

        erroMessage("Error!", "error on loading API");

    })

}

/**
 * Insert search results on Page
 * @param {Object} data_row
 */

function insertResultPage(data_row)
{
    var	rows = '';

    $("#listing").empty();

    rows = rows + '<div class="row">';
    rows = rows + '    <h7> Page '+ data_row.currentPage +' of '+ data_row.numberOfPages +' </h7>';
    rows = rows + '</div>';
    rows = rows + '<hr>';

    // **********

    $.each( data_row.data, function( key, value ) {

        rows = rows + '<div class="row">';
        rows = rows + '    <div class="col-md-12">';
        rows = rows + '         <div class="row no-gutters">';
        rows = rows + '             <div class="col-6 col-md-1" style="margin-right:15px;">';
        rows = rows + '                <img class="thumbnail" style="width:74px; height:74px;" width="64" height="64" src=" '+ value.image +' ">';
        rows = rows + '             </div>';
        rows = rows + '            <div class="col-12 col-sm-6 col-md-10">';
        rows = rows + '                 <h5> '+ value.name +' </h5>';
        rows = rows + '                <p>' + value.description +' </p>';
        rows = rows + '             </div>';
        rows = rows + '          </div>';
        rows = rows + '    </div>';
        rows = rows + '</div>';
        rows = rows + '<hr>';

    });

    $("#listing").append(rows);
}


/**
 *
 * Pagination
 *
 */
function insertPagination(currentPage, numberOfPages, totalResults)
{
    var	rows = '';
    var previous_page = currentPage - 1;
    var max_pagination = 0;

    if (numberOfPages > 1) {

        rows = rows + '<hr>';
        rows = rows + '<div class="row">';
        rows = rows + '    <nav aria-label="Page navigation example">';
        rows = rows + '        <ul class="pagination">';
        rows = rows + '            <li class="page-item disabled"><a class="page-link" href="#" onclick="updateDataSearch( '+ previous_page +' )">Previous</a></li>';
        rows = rows + '            <li class="page-item active"><a class="page-link" href="#">'+ currentPage +'</a></li>';

        if ((currentPage + 10) >= numberOfPages){
            max_pagination = numberOfPages;
        } else {
            max_pagination = currentPage + 10;
        }

        for (i = (currentPage + 1); i < max_pagination; i++) {
            rows = rows + '           <li class="page-item"><a class="page-link" href="#" onclick="updateDataSearch('+ i +')">'+ i +'</a></li>';
        }

        rows = rows + '            <li class="page-item"><a class="page-link" href="#">Next</a></li>';
        rows = rows + '    </ul>';
        rows = rows + '    </nav>';
        rows = rows + '</div>';

        $("#listing").append(rows);

    }

}


/**
 * Error Message
 * @param msg
 */
function erroMessage(msg) {
    $("#listing").empty();
    $("#listing").append(msg);
}