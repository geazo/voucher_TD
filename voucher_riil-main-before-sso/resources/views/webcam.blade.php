<!DOCTYPE html>
<html>

<head>
    <title>Guestbook Admin</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <style type="text/css">
        #results {
            width: 250px;
            height: 190px;
            border: 1px solid;
            background: #ccc;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="text-center">Penambahan Tamu</h1>

        <form method="POST" action="{{ route('webcam.capture') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <!-- Additional Fields for User Input -->
                    {{-- <input type="text" name="name" class="form-control" placeholder="Name" required /> <br /> --}}
                    {{-- <input type="text" name="email" class="form-control" placeholder="Email Address"
                        required /><br />
                    <input type="password" name="password" class="form-control" placeholder="Password" required /><br /> --}}
                    <input type="hidden" name="kategori" class="form-control" value="Visitor" /><br />
                    <input type="number" name="nik" class="form-control" placeholder="NIK" required /><br />
                    <input type="text" name="nama_depan" class="form-control" placeholder="Nama Depan"
                        required /><br />
                    <input type="text" name="nama_belakang" class="form-control" placeholder="Nama Belakang"
                        required /><br />
                    <input type="text" name="perusahaan" class="form-control" placeholder="Perusahaan"
                        required /><br />
                    <input type="number" name="no_hp" class="form-control" placeholder="No HP" required /><br />
                    <input type="text" name="kepentingan" class="form-control" placeholder="Kepentingan" required /><br />
                    <input type="text" name="lokasi_tujuan" class="form-control" placeholder="Lokasi Tujuan" required /><br />
                    {{-- <input type="text" name="visit_location" class="form-control" placeholder="Visit Location"
                        required /><br />
                    <input type="text" name="access_card" class="form-control" placeholder="Access Card"
                        required /><br /> --}}

                    <!-- Webcam Capture -->
                    <input type="button" class="btn btn-sm btn-primary" id="open_camera" value="Open Camera"><br />
                    <div id="my_camera" class="d-none"></div>
                    <br />
                    <input type="button" id="take_snap" value="Take Snapshot" onClick="take_snapshot()"
                        class="d-none btn btn-info btn-sm">
                    <input type="hidden" name="foto_ktp" class="image-tag">
                    <br />

                    <!-- Foto KTP Upload -->
                    {{-- <input type="file" name="foto_ktp" class="form-control" accept="image/*"><br /> --}}
                </div>
                <div class="col-md-6">
                    <div id="results"></div>
                </div>
                <div class="col-md-12 text-center">
                    <br />
                    <button class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>

    <script language="JavaScript">
        $("#open_camera").click(function() {
            $("#my_camera").removeClass('d-none');
            $("#take_snap").removeClass('d-none');

            Webcam.set({
                width: 250,
                height: 190,
                image_format: 'jpeg',
                jpeg_quality: 90
            });
            Webcam.attach('#my_camera');
        });

        function take_snapshot() {
            $("#my_camera").addClass('d-none');
            $("#take_snap").addClass('d-none');
            Webcam.snap(function(data_uri) {
                $(".image-tag").val(data_uri);
                document.getElementById('results').innerHTML = '<img src="' + data_uri + '"/>';
            });
        }
    </script>

</body>

</html>
