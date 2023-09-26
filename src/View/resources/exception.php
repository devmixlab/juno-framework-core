<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>[{{$code}}] {{$exception}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  </head>
  <body style="background-color: #f6f6f6;">
    <div class="px-3 py-3" style="max-width: 1100px; margin: auto;">

        <strong class="text-danger">Thrown new Exception: </strong>

          <div class="alert alert-danger show shadow-sm" role="alert">
            <strong>[Code: {{$code}}] {{$exception}}: </strong>
            <div>{{$message}}</div>
          </div>

        <div class="card text-bg-light mb-3 shadow-sm">
            <div class="card-header"><strong>Trace:</strong></div>
            <div class="card-body">
                <p class="card-text">{{$trace}}</p>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  </body>
</html>
