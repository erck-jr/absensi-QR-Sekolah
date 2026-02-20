@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl',
                }
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: "{{ session('error') }}",
                showConfirmButton: true,
                confirmButtonColor: '#d33',
                customClass: {
                    popup: 'rounded-xl',
                }
            });
        });
    </script>
@endif

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let errorMessages = '';
            @foreach($errors->all() as $error)
                errorMessages += '<li>{{ $error }}</li>';
            @endforeach
            
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan Validasi',
                html: '<ul class="text-left list-disc list-inside">' + errorMessages + '</ul>',
                showConfirmButton: true,
                confirmButtonColor: '#d33',
                customClass: {
                    popup: 'rounded-xl',
                }
            });
        });
    </script>
@endif
