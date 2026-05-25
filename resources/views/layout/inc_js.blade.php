
            <div class="modal fade modalHeadDecor" id="insurance_2" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="user_view">

                </div>
            </div>

          <script>
              function user_view(id){
                  $.ajax({
                      type: "GET",
                      url: "/user/"+id,
                      success: function(data) {
                          $("#user_view").html(data);

                            $('#select2Position2').select2({
                                placeholder: 'เลือกตำแหน่ง',
                                allowClear: true,
                                dropdownParent: $('#insurance_2'), // 💥 สำคัญมาก ถ้าอยู่ใน modal
                                width: '100%'
                            });

                            // $('#insurance').modal('show');
                            // setTimeout(() => {
                            //     console.log($('#user_view').html());
                            // }, 300);
                      }
                  });
              }
          </script>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/libs/i18n/i18n.js"></script>
    <script src="assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="assets/vendor/libs/moment/moment.js"></script>
    <script src="assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    {{-- <script src="assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script> --}}
    <script src="assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="assets/vendor/libs/select2/select2.js"></script>
    <script src="assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/vendor/js/dropdown-hover.js"></script>

    {{-- <script src="assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <script src="assets/js/extended-ui-sweetalert2.js"></script> --}}

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Page JS -->
    <!-- <script src="assets/js/dashboards-crm.js"></script> -->
    <script src="assets/js/forms-selects.js"></script>
    {{-- <script src="assets/js/forms-pickers.js"></script> --}}

    <script>
            // get_summary_menu()
            // function get_summary_menu(){
            //     $.ajax({
            //         type: "GET",
            //         url: "get-summary-menu",
            //         success: function(data) {
            //             $("#countBill").html(data.overdue_bill);
            //             $("#countBookingRoom").html(data.booking_room);
            //         }
            //     });
            // }

    </script>

