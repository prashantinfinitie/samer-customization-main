
// Shipping company


// Form submission for add/edit shipping company
// defensive binding: remove any previous handlers then bind
$(document).off('submit', '.add_shipping_company').on('submit', '.add_shipping_company', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]'); // use local submit button
    var originalBtnHtml = $submitBtn.html();

    // disable button & show waiting state
    $submitBtn.prop('disabled', true).html('Please wait...');

    var formData = new FormData(this);

    $.ajax({
        type: 'POST',
        url: $form.attr('action') && $form.attr('action') !== '' ? $form.attr('action') : (base_url + 'admin/shipping_companies/add_shipping_company'),
        data: formData,
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        success: function (result) {
            // re-enable button and restore text (choose appropriate label)
            var submitLabel = (result.message && result.message.indexOf('Added') !== -1) ? 'Add Shipping Company' : 'Update Shipping Company';
            $submitBtn.prop('disabled', false).html(submitLabel);

            if (result.error === false) {
                // hide modal, reset only this form, refresh table
                $form.closest('.modal').modal('hide');
                $form[0].reset();
                $('#shipping_company_data').bootstrapTable('refresh');

                // show toast (no blocking alert)
                if (typeof iziToast !== 'undefined') {
                    iziToast.success({
                        title: 'Success',
                        message: result.message || 'Success'
                    });
                } else {
                    // fallback
                    console.log('Success:', result.message);
                }
            } else {
                if (typeof iziToast !== 'undefined') {
                    iziToast.error({
                        title: 'Error',
                        message: result.message || 'Something went wrong'
                    });
                } else {
                    console.warn('Error:', result.message);
                }
            }

            // Update CSRF tokens if returned
            if (result.csrfName && result.csrfHash) {
                $('input[name="' + result.csrfName + '"]').val(result.csrfHash);
            }
        },
        error: function (xhr, status, error) {
            // restore button
            $submitBtn.prop('disabled', false).html(originalBtnHtml || 'Submit');

            if (typeof iziToast !== 'undefined') {
                iziToast.error({
                    title: 'Error',
                    message: 'An error occurred. Please try again.'
                });
            } else {
                console.error('AJAX error:', error);
            }
        }
    });

    // guarantee default submission doesn't continue
    return false;
});




$(document).on('click', '#delete-shipping-company', function () {
    var id = $(this).data('id');
    Swal.fire({
        title: 'Are You Sure !',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        showLoaderOnConfirm: true,
        preConfirm: function () {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: base_url + 'admin/shipping_companies/delete_shipping_company',
                    type: 'GET',
                    data: {
                        'id': id
                    },
                    dataType: 'json',
                })
                    .done(function (response, textStatus) {
                        if (response.error == false) {
                            Swal.fire('Deleted!', response.message, 'success');
                            $('table').bootstrapTable('refresh');
                        } else {
                            Swal.fire('Oops...', response.message, 'warning');
                        }
                    })
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR);
                        Swal.fire('Oops...', 'Something went wrong with ajax !', 'error');
                    });
            });
        },
        allowOutsideClick: false
    });
});




// Reusable Select2 initializer for "assign_zipcode" selects
function initAssignZipcode($select, dropdownParentSelector) {
    if (!$select || $select.data('select2-initialized')) return;

    $select.select2({
        placeholder: "Select Zipcodes",
        allowClear: true,
        dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $(document.body),
        ajax: {
            url: base_url + 'admin/shipping_companies/get_company_zipcodes',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return { id: item.id, text: item.zipcode };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // Mark as initialized so we don't re-init on the same element
    $select.data('select2-initialized', true);

    // If the server provided initial selected values as JSON in data-selected attribute,
    // populate them here so they show as selected in Select2.
    // Expected format for data-selected: [{"id":"12","zipcode":"400001"}, {"id":"23","zipcode":"400002"}]
    var selectedJson = $select.attr('data-selected');
    if (selectedJson) {
        try {
            var items = JSON.parse(selectedJson);
            items.forEach(function (it) {
                // create a new Option and append to the select
                var option = new Option(it.zipcode, it.id, true, true);
                $select.append(option);
            });
            $select.trigger('change');
        } catch (e) {
            console.warn('assign_zipcode: invalid data-selected JSON', e);
        }
    }
}

// when DOM ready, initialize all matching selects present on the page
$(function () {
    // Use class selector so both modal & page instances work. -> change your HTML select id to class "assign_zipcode"
    $('.assign_zipcode').each(function () {
        // if the select is inside a modal, use the modal as dropdownParent
        var $this = $(this);
        var $modal = $this.closest('.modal');
        var parent = $modal.length ? $modal : $(document.body);
        initAssignZipcode($this, parent);
    });
});

// For dynamically loaded content (like modal body loaded via AJAX), initialize when modal shown
$(document).on('shown.bs.modal', function (e) {
    var $modal = $(e.target);
    $modal.find('.assign_zipcode').each(function () {
        initAssignZipcode($(this), $modal);
    });
});




$(document).on('submit', "#add_shipping_company_form", function (e) {
    e.preventDefault();

    var data = new FormData(this);
    data.append(csrfName, csrfHash);
    data.append("country_code", $(".selected-dial-code").text());
    $.ajax({
        type: "POST",
        url: base_url + 'shipping_company/login/create_shipping_company',
        data: data,
        processData: !1,
        contentType: !1,
        cache: !1,
        dataType: "json",

        success: function (response) {

            csrfName = response.csrfName,
                csrfHash = response.csrfHash;
            if (response.error == true) {
                iziToast.error({
                    message: response.message,
                });

            } else {
                iziToast.success({
                    message: response.message,
                });
                setTimeout(function () {
                    window.location.href = base_url + 'shipping_company/login';
                }, 3000)

            }
        }
    })
});



// cash collection and fund transfer

// JavaScript for Shipping Company Cash Collection

$(document).ready(function () {
    // // Date picker initialization
    // $('#datepicker').daterangepicker({
    //     locale: {
    //         format: 'DD-MM-YYYY'
    //     }
    // });

    // Handle edit cash collection button
    $(document).on('click', '.edit_cash_collection_btn', function () {
        var id = $(this).data('id');
        var order_id = $(this).data('order-id');
        var amount = $(this).data('amount');
        var company_id = $(this).data('company-id');

        $('#transaction_id').val(id);
        $('#order_id').val(order_id);
        $('#amount').val(amount);
        $('#order_amount').val(amount);
        $('#shipping_company_id').val(company_id);

        $('#details').val('Order ID: ' + order_id + '\nAmount: ' + amount);
    });
});

// Query params for cash collection table
function cash_collection_query_params(p) {
    return {
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        filter_status: $('#filter_status').val(),
        filter_company: $('#filter_company').val()
    };
}

// Filter function
function status_date_wise_search() {
    var date = $('#datepicker').val();
    if (date !== '') {
        var dates = date.split(' - ');
        var start_date = dates[0].split('-').reverse().join('-');
        var end_date = dates[1].split('-').reverse().join('-');
        $('#start_date').val(start_date);
        $('#end_date').val(end_date);
    }

    $('table').bootstrapTable('refresh');
}

// Reset form on modal close
$('#cash_collection_model').on('hidden.bs.modal', function () {
    $(this).find('form')[0].reset();
    $('#transaction_id').val('');
    $('#order_id').val('');
    $('#shipping_company_id').val('');
    $('#details').val('');
});
