function loadFile(event, previewId) {
    var input = event.target;
    var preview = document.getElementById(previewId);
    if (!input || !input.files || !input.files[0] || !preview) {
        return;
    }
    preview.src = URL.createObjectURL(input.files[0]);
    preview.classList.remove('d-none');
    preview.style.display = 'block';
}

/** UTF-8 safe base64 — avoids LiteSpeed/ModSecurity 403s on rich HTML in POST bodies */
function ccUtf8ToBase64(str) {
    return btoa(unescape(encodeURIComponent(str || '')));
}

/**
 * Before submit: move Summernote HTML into description_b64 and stop posting raw HTML tags.
 */
$(document).on('submit', 'form.cc-encode-richtext', function () {
    var $form = $(this);
    $form.find('textarea.summernote, textarea[name="description"]').each(function () {
        var $ta = $(this);
        var html = '';
        if ($ta.next('.note-editor').length && typeof $ta.summernote === 'function') {
            html = $ta.summernote('code');
        } else {
            html = $ta.val() || '';
        }
        $ta.removeAttr('name');
        var $hidden = $form.find('input[name="description_b64"]');
        if (!$hidden.length) {
            $hidden = $('<input type="hidden" name="description_b64">').appendTo($form);
        }
        $hidden.val(ccUtf8ToBase64(html));
    });
});

$(document).ready(function(){
    // Check admin password is correct or not
    $("#current_pwd").keyup(function(){
        var current_pwd = $("#current_pwd").val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:'post',
            url:BaseadminUrl + '/' +'check_current_password',
            data:{current_pwd:current_pwd},
            success:function(resp){
                if(resp == "false"){
                    $("#vefiryCurrentPwd").html("Old Password is Incorrect !");
                }else if(resp == "true"){
                    $("#vefiryCurrentPwd").html("Old Password is correct !");
                }
            },error:function(){
                alert('Error');
            }
        })
    });
});

// update recipe of the week status
$(document).on('click', '.updateRecipeOftheWeekStatus', function(){
    var status = $(this).children('i').attr('status');
    var recipe_id = $(this).attr('recipe_id');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'get',
        url: 'get-current-recipe-of-the-week',
        success: function(currentRecipe) {
            if (currentRecipe && Object.keys(currentRecipe).length > 0 && currentRecipe.id) {
                if (currentRecipe.id != recipe_id && status == 'Inactive') {
                    alert("You can make only one recipe of the month. Please disable the current one first.");
                    return; 
                }
            }
            
            const confirmationMessage = status == 'Inactive'
                ? "Are you sure you want to make this recipe the Recipe of the Week?"
                : "Are you sure you want to remove this recipe from Recipe of the Week?";

            if (confirm(confirmationMessage)) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'post',
                    url: 'update-recipeoftheweek-status',
                    data: { status: status, recipe_id: recipe_id },
                    success: function(resp) {
                        if(resp['status'] == 0){
                            $("#recipe-"+recipe_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
                        } else if(resp['status'] == 1){
                            $("#recipe-"+recipe_id).html("<i class='fas fa-toggle-on' style='color:#E15C0A' status='Active'></i>");
                        }

                        setTimeout(function(){
                            window.location.reload();
                        }, 500);
                    },
                    error: function() {
                        alert('Error');
                    }
                });
            }
        },
        error: function() {
            alert("Failed to check the current Recipe of the Week.");
        }
    });
});


// update monthly challenge status
$(document).on('click', '.updateMonthlyChallengeStatus', function(){
    var status = $(this).children('i').attr('status');
    var monthly_challenge_id = $(this).attr('monthly_challenge_id');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'get',
        url: 'get-current-monthly-challenge',
        success: function(currentMonthlyChallenge) {
            if (currentMonthlyChallenge && Object.keys(currentMonthlyChallenge).length > 0 && currentMonthlyChallenge.id) {
                if (currentMonthlyChallenge.id != monthly_challenge_id && status == 'Inactive') {
                    alert("You can make only one monthly challenge. Please disable the current one first.");
                    return; 
                }
            }
            
            const confirmationMessage = status == 'Inactive'
                ? "Are you sure you want to make this Monthly Challenge?"
                : "Are you sure you want to remove this from Monthly Challenge?";

            if (confirm(confirmationMessage)) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'post',
                    url: 'update-monthlyChallenge-status',
                    data: { status: status, monthly_challenge_id: monthly_challenge_id },
                    success: function(resp) {
                        if(resp['status'] == 0){
                            $("#monthly_challenge-"+monthly_challenge_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
                        } else if(resp['status'] == 1){
                            $("#monthly_challenge-"+monthly_challenge_id).html("<i class='fas fa-toggle-on' style='color:#E15C0A' status='Active'></i>");
                        }

                        setTimeout(function(){
                            window.location.reload();
                        }, 500);
                    },
                    error: function() {
                        alert('Error');
                    }
                });
            }
        },
        error: function() {
            alert("Failed to check the current Monthly Challenge.");
        }
    });
});

/* ------- Popular Recipe Status ----------*/
// $(document).ready(function() {
//     let previousValue;
//     $(document).on('focus', '.statusChange', function () {
//         // Store the current value before the change
//         previousValue = $(this).val();
//     });
    
//     $(document).on('change', '.statusChange', function () {
//         const selectElement = $(this);
//         const status = selectElement.val();
//         const id = selectElement.data('id');
        
//         $.ajax({
//             headers: {
//                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//             },
//             type: 'get',
//             url: 'check-popular-recipe-count',
//             success:function(popularRecipeCount) {
//                 if (popularRecipeCount >= 2 && status == "1") { 
//                     alert("You cannot make more than 2 recipes popular. Disable one first.");
//                     selectElement.val(previousValue); // Revert to previous value
//                     return; 
//                 }
                
//                 const confirmationMessage = status == "1" 
//                     ? "Are you sure you want to make this recipe popular?" 
//                     : "Are you sure you want to remove this recipe from popular?";
            
//                 if (confirm(confirmationMessage)) {
//                     $.ajax({
//                         headers: {
//                             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                         },
//                         url: 'update-status',
//                         method: 'POST',
//                         data: {
//                             id: id,
//                             status: status,
//                         },
//                         success: function (response) {
//                             if (response.success) {
//                                 toastr.success(response.message);
//                             } else {
//                                 toastr.error(response.message || 'Failed to update status.');
//                             }
                            
//                             window.location.reload();
//                         },
                        
//                         error: function (xhr) {
//                             toastr.error('An error occurred while updating status.');
//                         }
//                     });
//                 } else {
//                     // Revert the dropdown to its previous value if canceled
//                     selectElement.val(previousValue);
//                 }
//             },
//             error: function() {
//                 alert("Failed to update the Popular recipe status.");
//             }
//         })
//     });
    
// });


$(document).ready(function() {
    let previousValue;

    $(document).on('focus', '.statusChange', function () {
        previousValue = $(this).val(); // Store the current value
    });

    $(document).on('change', '.statusChange', function () {
        const selectElement = $(this);
        const status = selectElement.val();
        const id = selectElement.data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: 'check-popular-recipe-count',
            success: function(response) {
                const popularRecipeCount = parseInt(response);
                
                if (popularRecipeCount >= 4 && status == "1") { 
                    alert("You cannot make more than four popular recipes. Disable one first.");
                    selectElement.val(previousValue); // Revert to previous value
                    return; 
                }

                const confirmationMessage = status == "1" 
                    ? "Are you sure you want to make this recipe popular?" 
                    : "Are you sure you want to remove this recipe from popular?";

                if (confirm(confirmationMessage)) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: 'update-status',
                        method: 'POST',
                        data: { id: id, status: status },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                window.location.reload();
                            } else {
                                toastr.error(response.message || 'Failed to update status.');
                                selectElement.val(previousValue);
                            }
                        },
                        error: function() {
                            toastr.error('An error occurred while updating status.');
                            selectElement.val(previousValue);
                        }
                    });
                } else {
                    selectElement.val(previousValue); // Revert if canceled
                }
            },
            error: function() {
                alert("Failed to check the Popular recipe count.");
                selectElement.val(previousValue); // Revert in case of error
            }
        });
    });
});




// update featured status
$(document).on('click', '.updateFeaturedStoryStatus', function(){
    var status = $(this).children('i').attr('status');
    var story_id = $(this).attr('story_id');
    
    const confirmationMessage = status == 'Inactive'
        ? "Are you sure you want to make this story featured?"
        : "Are you sure you want to remove this story from featured?";

    if(confirm(confirmationMessage)) {
        $.ajax({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: 'featured-stories-status',
            type : 'post',
            data : {status:status,story_id:story_id},
            success:function(resp){
                if(resp['status'] == 0){
                    $("#story-"+story_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
                }else if(resp['status'] ==1){
                    $("#story-"+story_id).html("<i class='fas fa-toggle-on' style='color:#E15C0A' status='Active'></i>");
                }
                
                window.location.reload();
            },error:function(){
                alert('Error');
            }
        });
    }
});

// popular recipe status
function handleStatusChange(selector, url, messages) {
    let previousValue;

    $(document).on('focus', selector, function () {
        // Store the current value before the change
        previousValue = $(this).val();
    });
    
    $(document).on('change', selector, function () {
        const selectElement = $(this);
        const status = selectElement.val();
        const id = selectElement.data('id');

        const confirmationMessage = status == "1" 
            ? messages.confirmActivate 
            : messages.confirmDeactivate;

        if (confirm(confirmationMessage)) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                method: 'POST',
                data: {
                    id: id,
                    status: status,
                },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Failed to update status.');
                    }
                    
                    window.location.reload();
                },
                
                error: function (xhr) {
                    toastr.error('An error occurred while updating status.');
                }
            });
        } else {
            // Revert the dropdown to its previous value if canceled
            selectElement.val(previousValue);
        }
    });
}



// Usage for recipes
// handleStatusChange('.statusChange', 'update-status', {
//     confirmActivate: "Are you sure you want to make this recipe popular?",
//     confirmDeactivate: "Are you sure you want to remove this recipe from popular?"
// });

// Usage for stories
handleStatusChange('.storystatusChange', 'update-best-from-the-champs-status', {
    confirmActivate: "Are you sure you want to make this story best of the champs?",
    confirmDeactivate: "Are you sure you want to remove this story from best of the champs?"
});

// update SubCategory status
$(document).on('click', '.updateGrowingActivityStatus', function(){
    var status = $(this).children('i').attr('status');
    var growing_activity_id = $(this).attr('growing_activity_id');
    $.ajax({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type : 'post',
        url : (typeof BaseadminUrl !== 'undefined' ? BaseadminUrl + '/' : '') + 'update-growing-activity-status',
        data : {status:status,growing_activity_id:growing_activity_id},
        success:function(resp){
            if(resp['status'] == 0){
                $("#growing_activity-"+growing_activity_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
            }else if(resp['status'] ==1){
                $("#growing_activity-"+growing_activity_id).html("<i class='fas fa-toggle-on' style='color:#E15C0A' status='Active'></i>");
            }
        },error:function(){
            alert('Error');
        }
    });
});

// update SubCategory status
$(document).on('click', '.updateSubCategoryStatus', function(){
    var status = $(this).children('i').attr('status');
    var subcategory_id = $(this).attr('subcategory_id');
    // alert(subcategory_id);
    $.ajax({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type : 'post',
        url : 'update-subcategory-status',
        data : {status:status,subcategory_id:subcategory_id},
        success:function(resp){
            if(resp['status'] == 0){
                $("#subcategory-"+subcategory_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
            }else if(resp['status'] ==1){
                $("#subcategory-"+subcategory_id).html("<i class='fas fa-toggle-on' style='color:#E15C0A' status='Active'></i>");
            }
        },error:function(){
            alert('Error');
        }
    });
});

// update type of business status
$(document).on('click', '.updateTOBStatus', function(){
    var status = $(this).children('i').attr('status');
    var tob_id = $(this).attr('tob_id');
    
    $.ajax({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type : 'POST',
        url : 'update-tob-status',
        data : {status:status,tob_id:tob_id},
        success:function(resp){
            if(resp['status'] == 0){
                $("#tob-"+tob_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
            }else if(resp['status'] ==1){
                $("#tob-"+tob_id).html("<i class='fas fa-toggle-on' style='color:#E15C0A' status='Active'></i>");
            }
        },error:function(){
            alert('Error');
        }
    });
});

// alert fade-out
$('#validation-alert').fadeOut(4000);

/**
 * Shared DataTables defaults (responsive, mobile-friendly).
 * Initializes only tables that exist on the current page.
 */
function initCookingChampsDataTables() {
    if (typeof $.fn.DataTable === 'undefined') {
        return;
    }

    var dtDefaults = {
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        pageLength: 25,
    };

    var tableIds = [
        'example2',
        'categories',
        'subcategories',
        'users',
        'employees',
        'typeofBusiness',
        'companyActivity',
        'regionAndDepartment',
        'insuranceDecennial',
        'qualification',
        'parent_stories',
        'banners',
        'growing_activity',
        'product_categories',
        'subscription',
    ];

    tableIds.forEach(function (id) {
        var $table = $('#' + id);
        if (!$table.length) {
            return;
        }
        if ($.fn.dataTable.isDataTable('#' + id)) {
            return;
        }
        try {
            $table.DataTable(dtDefaults);
        } catch (e) {
            console.warn('DataTables init skipped for #' + id, e);
        }
    });
}

$(document).ready(function () {
    initCookingChampsDataTables();
});


/* --- Common Delete Function for all the modules */
$(document).on('click', ".confirmDelete", function(){
   var record = $(this).attr('record');
   var record_id = $(this).attr('record_id');
   Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#48335C',
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
    if (result.isConfirmed) {
        var root = $('meta[name="app-base-url"]').attr('content') || $('#rootUrl').val() || '';
        root = String(root).replace(/\/+$/, '');
        var url = root + '/admin/' + record + '/' + encodeURIComponent(record_id);
        var token = $('meta[name="csrf-token"]').attr('content');

        var form = $('<form>', { method: 'POST', action: url });
        form.append($('<input>', { type: 'hidden', name: '_token', value: token }));
        $('body').append(form);
        form.trigger('submit');
    }
    });
});
