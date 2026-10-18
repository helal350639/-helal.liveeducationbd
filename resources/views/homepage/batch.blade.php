@php
$user = Auth::guard('student')->user();
@endphp

@extends('homepage')
@section('title', 'কোর্স সমূহ')
@section('content')

<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .course-image{width:100%}
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          
          <div class="title-area text-center">
            <h1>{{$type ?? ""}}</h1>
            {{-- <small>চলমান ব্যাচ সমূহ </small> --}}
          </div>
          <div class="all-courses-area">
            <div class="row g-2 g-md-3 g-lg-4">
  
              @if(count($batches))
                @foreach($batches as $value)
                <div class="col-6 col-lg-3 mt-4">
                  <div class="exam-package-area">
                    <div class="package-exam-image">
                      <a href="{{route('category.department', [$type, $value->id, $dashboard])}}">
                      <img src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" loading="lazy" />
                      </a>
                    </div>
                    <div class="package-exam-content mx-2 mx-lg-3">
                      <div class="package-exam-title pt-3">
                        <h2>
                          <a href="{{route('category.department', [$type, $value->id])}}">{{$value->name}}</a>
                        </h2>
                      </div>
                      <div class="row button-and-price pb-3 pb-lg-4">
                        <div class="col">
                          <div class="package-exam-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                          </div>
                        </div>
                        <div class="col text-end">
                          <div class="package-exam-button">
                            <div class="package-exam-details">
                              <a href="{{route('category.department', [$type, $value->id])}}">View Details </a>
                            </div>
                          </div>
                      
                          <div class="package-exam-price">
                            <div class="package-exam-discount-price text-right">
                              @if(isset($value->items_count))
                              ({{ $value->items_count }})
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              @else
              <div class="card card-default">
                <div class="card-body text-center">
                  No Batch Available
                </div>
              </div>
              @endif
            </div>
          </div>
        </div> <!-- /.row -->
        
        @include('partials.back-button')

      </div>
    </section> <!-- /.content -->



    <style>

/* =========================================================
   COURSE / BATCH PAGE
========================================================= */

.our_courses {
    width: 100%;
    padding: 35px 0 50px;
    background: #f5f8fd;
}


/* CONTAINER */

.our_courses .container {
    max-width: 1250px;
}


/* =========================================================
   TITLE
========================================================= */

.our_courses .title-area {
    margin-bottom: 20px;
}

.our_courses .title-area h1 {
    margin: 0;
    color: #12345b;
    font-size: 30px;
    font-weight: 700;
}


/* =========================================================
   CARD GRID
========================================================= */

.all-courses-area {
    width: 100%;
}

.all-courses-area .row {
    align-items: stretch;
}


/* =========================================================
   COURSE CARD
========================================================= */

.exam-package-area {
    height: 100%;
    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e3eaf3;

    border-radius: 16px;

    box-shadow:
        0 5px 15px rgba(30, 70, 110, .08);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.exam-package-area:hover {

    transform: translateY(-4px);

    box-shadow:
        0 10px 25px rgba(30, 70, 110, .14);

}


/* =========================================================
   IMAGE
========================================================= */

.package-exam-image {
    width: 100%;
    height: 180px;

    overflow: hidden;

    background: #eef2f7;
}


.package-exam-image a {
    display: block;
    width: 100%;
    height: 100%;
}


.package-exam-image img {

    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;

    transition: transform .3s ease;

}


.exam-package-area:hover
.package-exam-image img {

    transform: scale(1.03);

}


/* =========================================================
   CONTENT
========================================================= */

.package-exam-content {

    padding: 0 4px;
}


/* =========================================================
   TITLE
========================================================= */

.package-exam-title {

    padding-top: 14px !important;

}


.package-exam-title h2 {

    margin: 0 0 10px;

    line-height: 1.35;

}


.package-exam-title h2 a {

    color: #12345b;

    font-size: 18px;

    font-weight: 700;

    text-decoration: none;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;

}


.package-exam-title h2 a:hover {

    color: #1677ff;

}


/* =========================================================
   BOTTOM AREA
========================================================= */

.button-and-price {

    margin-top: 8px;

    align-items: center;

}


/* =========================================================
   RATING
========================================================= */

.package-exam-rating {

    display: flex;

    align-items: center;

    gap: 2px;

}


.package-exam-rating i {

    font-size: 13px;

    color: #fbbf24;

}


/* =========================================================
   DETAILS BUTTON
========================================================= */

.package-exam-details a {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 36px;

    padding: 7px 13px;

    color: #1677ff;

    background: linear-gradient(
        180deg,
        #edf5ff,
        #dceaff
    );

    border: 1px solid #d1e1f7;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    white-space: nowrap;

    box-shadow:
        0 2px 0 #c4d5e9;

    transition: .15s ease;

}


.package-exam-details a:hover {

    color: #ffffff;

    background: linear-gradient(
        180deg,
        #3d91ff,
        #1677ff
    );

    border-color: #1677ff;

    transform: translateY(-1px);

}


/* =========================================================
   PRICE / COUNT
========================================================= */

.package-exam-discount-price {

    color: #64748b;

    font-size: 13px;

}


/* =========================================================
   NO BATCH
========================================================= */

.all-courses-area .card {

    width: 100%;

    border: none;

    border-radius: 12px;

    box-shadow: 0 4px 12px rgba(0,0,0,.06);

}


/* =========================================================
   SCROLLBAR
========================================================= */

.panel::-webkit-scrollbar {

    width: 5px;

}

.panel::-webkit-scrollbar-thumb {

    background: #d5dce5;

    border-radius: 10px;

}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 991px) {

    .our_courses {

        padding: 25px 0 40px;

    }


    .our_courses .container {

        padding-left: 15px;
        padding-right: 15px;

    }


    .package-exam-image {

        height: 155px;

    }


    .package-exam-title h2 a {

        font-size: 16px;

    }

}


/* =========================================================
   MOBILE
   2 CARDS PER ROW
========================================================= */

@media (max-width: 767px) {

    .our_courses {

        padding: 20px 0 35px;

    }


    .our_courses .container {

        width: 100%;

        padding-left: 8px;
        padding-right: 8px;

    }


    .our_courses .title-area {

        margin-bottom: 12px;

    }


    .our_courses .title-area h1 {

        font-size: 21px;

    }


    .all-courses-area .row {

        --bs-gutter-x: 8px;
        --bs-gutter-y: 12px;

    }


    /* 2 COLUMN */

    .all-courses-area .col-6 {

        width: 50%;

        padding-left: 4px;
        padding-right: 4px;

        margin-top: 8px !important;

    }


    /* CARD */

    .exam-package-area {

        border-radius: 11px;

        box-shadow:
            0 3px 10px rgba(30,70,110,.08);

    }


    /* IMAGE */

    .package-exam-image {

        height: 105px;

    }


    /* CONTENT */

    .package-exam-content {

        margin-left: 7px !important;
        margin-right: 7px !important;

    }


    .package-exam-title {

        padding-top: 9px !important;

    }


    .package-exam-title h2 {

        margin-bottom: 5px;

    }


    .package-exam-title h2 a {

        font-size: 12px;

        line-height: 1.3;

    }


    /* BOTTOM */

    .button-and-price {

        padding-bottom: 10px !important;

        margin-top: 4px;

    }


    /* RATING */

    .package-exam-rating i {

        font-size: 8px;

    }


    /* BUTTON */

    .package-exam-details a {

        min-height: 29px;

        padding: 5px 7px;

        border-radius: 6px;

        font-size: 9px;

        box-shadow:
            0 2px 0 #c4d5e9;

    }


    .package-exam-discount-price {

        font-size: 9px;

    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 380px) {

    .our_courses .container {

        padding-left: 6px;
        padding-right: 6px;

    }


    .package-exam-image {

        height: 90px;

    }


    .package-exam-title h2 a {

        font-size: 11px;

    }


    .package-exam-details a {

        font-size: 8px;

        padding: 4px 6px;

    }

}

</style>

<script></script>
@endsection