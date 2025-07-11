@php
$student  = [];
$user = Auth::guard('student')->user();
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
@endphp

@extends('homepage')
@section('title', 'হোম')
@section('content')

<!-- preloader --> 
<div class="preloader text-center">
  <img src="/web/img/preloader.gif" alt="">
</div>

<section id="Home_add">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-2" style="padding: calc(var(--bs-gutter-x)* .0)!important;">
        <div class="home-1st-add-image">
          <a href="{{route('home.coming-soon')}}"><img src="/web/img/ad1.jpg" alt="" srcset="" loading="lazy"></a>
        </div>
      </div>
      <div class="col-lg-7 col-md-12" style="
        padding-left: calc(var(--bs-gutter-x) * 0.08) !important;
        padding-right: calc(var(--bs-gutter-x) * 0.08) !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;">
        <div class="home-2nd-add-image">
          <a href="#">
            <img src="/web/img/eid_mubarak.jpg?v=1.12" alt="" srcset="" loading="lazy">
          </a>
        </div>
      </div>
      <div class="col-lg-3" style="padding: 0 !important;">
        <div class="home-3rd-add-image dropdown">
          <a href="{{route('home.coming-soon')}}" class="dropdown-/toggle" id="home3rdAddDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="/web/img/ad3.jpg" alt="" loading="lazy" srcset="">
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="Home_main_banner" class="background-res background-ats py-3 py-lg-5 d-lg-block" style="background-image: url('/web/img/Background_Noise.jpg')">
  <div class="container">
    <div class="row gy-4">
      <!-- <div class="col-lg-3 col-md-12">
      </div> -->
      <div class="col-lg-12 col-md-12">
          <div class="row">
            <div class="col-md-12 col-lg-8">
              <div class="home-1st-slide">
                <div class="variable-width">

                  @foreach($sliders as $value)

                    <div class="hero-slide">
                      <div class="exam-package-area">
                          <div class="package-exam-image">
                            <a href="{{route('home.course.show', $value->id)}}">
                            <img src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" loading="lazy" srcset="" />
                            </a>
                          </div>
                          <div class="package-exam-content" id="custom-package-exam-content">
                            <div class="package-exam-title pt-3" id="custom-package-exam-title">
                              <h2><a href="{{route('home.course.show', $value->id)}}">{{mb_substr($value->name, 0, 18)}}</a></h2>
                            </div>
                            <div class="row gy-2 button-and-price">
                                <div class="col-12">
                                  <div class="package-exam-rating">
                                      <i class="fas fa-star"></i>
                                      <i class="fas fa-star"></i>
                                      <i class="fas fa-star"></i>
                                      <i class="fas fa-star"></i>
                                      <i class="far fa-star"></i>
                                  </div>
                                </div>
                                <div class="col-12">
                                  <a href="{{route('home.course.show', $value->id)}}" class="btn btn_warning text-white bg-brand w-100"> বিস্তারিত দেখুন <i class="fa-solid fa-arrow-right"></i></a>
                                </div>
                            </div>
                          </div>
                      </div>
                    </div>

                    @endforeach
                </div>
              </div>
            </div>
            <div class="col-md-12 col-lg-4">
              <div class="main-banner-video">
                <div class="ratio ratio-16x9" style="width: 100%; height: 0; padding-bottom: 49.25%;" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                  <iframe id="videoIframe2" src="https://www.youtube.com/embed/lEw6fQwQXZM?si=myZrYsOTKstGK0zx" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                  <!-- Transparent overlay to trigger the click event -->
                  <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                  </div>
                </div>
              </div>
              <div class="home-video-feature-area mt-2">
                <div class="row">
                  <div class="col-4">
                    <div class="home-feature-video">
                      <div class="ratio ratio-1x1"
                        style="position: relative; width: 100%; height: 0; padding-bottom: 100%;">
                        <img class="img img-thumbnail-custom lazyload" src="/web/img/video-icon.png" alt="Recent Job Solution Banner" style="position: absolute; top: 9%; left: 0; width: 100%; height: 76%;" srcset="">
                        <!-- Transparent overlay to trigger the click event -->
                        <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="home-feature-video">
                        <div class="ratio ratio-1x1"
                          style="position: relative; width: 100%; height: 0; padding-bottom: 100%;">
                          <img class="img img-thumbnail-custom lazyload" src="/web/img/video-icon.png" alt="Teachers Tips & Advice Banner" style="position: absolute; top: 9%; left: 0; width: 100%; height: 76%;" srcset="">
                          <!-- Transparent overlay to trigger the click event -->
                          <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')"
                              style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                          </div>
                        </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="home-feature-video">
                      <div class="ratio ratio-1x1" style="position: relative; width: 100%; height: 0; padding-bottom: 100%;">
                        <img class="img img-thumbnail-custom lazyload" src="/web/img/video-icon.png" alt="APP Website Uses Rules Banner" style="position: absolute; top: 9%; left: 0; width: 100%; height: 76%;" srcset="">
                        <!-- Transparent overlay to trigger the click event -->
                        <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="home-feature-video">
                      <div class="ratio ratio-1x1" style="position: relative; width: 100%; height: 0; padding-bottom: 100%;">
                        <img class="img img-thumbnail-custom lazyload" src="/web/img/video-icon.png" alt="APP Website Uses Rules Banner" style="position: absolute; top: 9%; left: 0; width: 100%; height: 76%;" srcset="">
                        <!-- Transparent overlay to trigger the click event -->
                        <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="home-feature-video">
                      <div class="ratio ratio-1x1" style="position: relative; width: 100%; height: 0; padding-bottom: 100%;">
                        <img class="img img-thumbnail-custom lazyload" src="/web/img/video-icon.png" alt="APP Website Uses Rules Banner" style="position: absolute; top: 9%; left: 0; width: 100%; height: 76%;" srcset="">
                        <!-- Transparent overlay to trigger the click event -->
                        <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="home-feature-video">
                      <div class="ratio ratio-1x1" style="position: relative; width: 100%; height: 0; padding-bottom: 100%;">
                        <img class="img img-thumbnail-custom lazyload" src="/web/img/video-icon.png" alt="APP Website Uses Rules Banner" style="position: absolute; top: 9%; left: 0; width: 100%; height: 76%;" srcset="">
                        <!-- Transparent overlay to trigger the click event -->
                        <div onclick="showVideoModal('https://www.youtube.com/embed/lEw6fQwQXZM?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
      </div>
    </div>
  </div>
</section>

<section id="Home_category" class="background-res background-ats py-5" style="background: #eef">
  <div class="container">
    <div class="row mb-5">
      <div class="title-area text-center">
        <h1 class="fw-bold custom-section-title">
          <span>একটি প্ল্যাটফর্মেই </span>-প্রস্তুতি হবে সকল চাকুরির
        </h1>
      </div>
    </div>

    <div class="emergency-course-section d-flex justify-content-around flex-wrap">
      <a href="{{route('category.courses', 'job')}}" class="emergency-box">
        <img src="/web/img/job-preparation.png" alt="Free Studyroom" class="img-fluid">
        <p class="pointer-cursor text-center">Job Preparation</p>
      </a>

      <a href="{{route('category.batches', 'Premium Exams')}}" class="emergency-box">
        <img src="/web/img/live-exam.png" alt="All Job Guidelines & Techniques" class="img-fluid">
        <p class="pointer-cursor text-center">Online live exam premium</p>
      </a>

      <a href="{{route('category.batches', 'Free Exams')}}" class="emergency-box">
        <img src="/web/img/exam.png" alt="" class="img-fluid">
        <p class="pointer-cursor text-center">Free Exams</p>
      </a>

      <a href="#" class="emergency-box">
        <img src="/web/img/online-class.png" alt="Leader Board" class="img-fluid">
        <p class="pointer-cursor text-center">Online Classes</p>
      </a>

      <a href="#" class="emergency-box">
        <img src="/web/img/recorded-course.png" alt="Students Review" class="img-fluid">
        <p class="pointer-cursor text-center">Recorded Courses</p>
      </a>

      <a href="{{route('category.departments', 'Polytechnic')}}" class="emergency-box">
        <img src="/web/img/politechnic.jpg" alt="" class="img-fluid">
        <p class="pointer-cursor text-center">Polytechnic Study</p>
      </a>

    </div>

  </div>
</section>

  <section id="exam-section" class="our-service background-res background-ats py-5" style="background-image: url('/web/img/background-bg.jpg');">
    <div class="container py-3">
      <div class="row">
        <div class="title-area text-center">
          <h2 class="fw-bold mb-4 custom-section-title">Exam Section<br></h2>
        </div>
      </div>
      <div class="home-services-area exam-area">
        <div class="row" style="--bs-gutter-x: 0.3rem !important;">
          <div class="col-md-2 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
            <a href="{{route('category.batches', 'Free Exams')}}">
              <div class="my-home-service premium-course-box">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>Free Exam</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-2 col-6 mb-1" id="home-services-area-two" data-toggle="tooltip" data-placement="bottom" title="Tooltip on bottom">
            <a href="{{route('category.batches', 'Premium Exams')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>Premium Exam</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-2 col-6 mb-1" id="home-services-area-three">
            <a href="{{route('category.result.option')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>Results</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-2 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('category.batches', 'Exam Routine')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>Exam Routine</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-2 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('category.batches', 'Exam PDF')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>Exam PDF</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-2 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>Written Exam</h3>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="our_courses" class="our_courses">
    <div class="container">
      <div class="row mb-5">
        <div class="title-area text-center">
          <h2 class="fw-bold custom-section-title"> চলমান  <span>কোর্স সমূহ</span> </h2>
        </div>
      </div>

      <div class="home-category-area mt-5">
        <div class="row justify-content-center">

          @foreach($courses as $key => $course)

          <div class="home-category-item" id="category-item-1">
            <a href="#">
              <div class="my-course-category">
                <div class="my-course-category-icon">
                  <i class="fas fa-graduation-cap"></i>
                </div>
                <br>
                <div class="my-course-category-content">
                  <h3>{{$course->name}} </h3>
                </div>
              </div>
            </a>
          </div>

          @endforeach
          
        </div>
      </div>

      <div class="all-courses-area">
        <div class="row g-2 g-md-3 g-lg-4">

          @foreach($batches as $batch)

          <div class="col-6 col-lg-3 mt-4">
            <div class="exam-package-area">
              <div class="package-exam-image">
                <a href="{{route('home.course.show', $batch->id)}}">
                <img src="{{ $batch->banner? $batch->banner : '/img/course.png'}}" alt="" loading="lazy" />
                </a>
              </div>
              <div class="package-exam-content mx-2 mx-lg-3">
                <div class="package-exam-title pt-3">
                  <h2><a href="{{route('home.course.show', $batch->id)}}">{{mb_substr($batch->name, 0, 18)}}</a></h2>
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
                    <div class="package-exam-price">
                      <div class="package-exam-total-price text-muted">
                        <s class="text-muted">৳ {{number_format($batch->price, 0)}}</s>
                      </div>
                      <div class="package-exam-discount-price">৳ {{number_format($batch->price - $batch->discount, 0)}}</div>
                    </div>
                  </div>
                  <div class="col text-end">
                    <div class="package-exam-button">
                      <div class="package-exam-details">
                        <a href="{{route('home.course.show', $batch->id)}}">View Details </a>
                      </div>
                      <a href="{{route('home.course.show', $batch->id)}}">
                        <div class="custom-btn btn-12">
                          <span>ক্লিক করুন!</span><span>কোর্সটি কিনুন</span>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          @endforeach

      </div>
      <div class="see-more-button text-center mt-5">
        <a href="{{route('home.course')}}" type="button" class="btn btn_warning">See More</a>
      </div>
      </div>
    </div>
  </section>

  <section id="book-storage" class="our-service background-res background-ats py-5"
  style="background-image: url('/web/img/Background_Noise.jpg')">
    <div class="container py-3">
      <div class="row">
        <div class="title-area text-center">
          <h2 class="fw-bold mb-4 custom-section-title">Premium Book Storage <br></h2>
        </div>
      </div>
      <div class="home-services-area">
        <div class="row" style="--bs-gutter-x: 0.3rem !important;">
          <div class="col-md-3 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>ডিপ্লোমা জব প্রস্তুতি বই ডিপার্ট্মেন্ট </h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1" id="home-services-area-two" data-toggle="tooltip" data-placement="bottom" title="Tooltip on bottom">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>রিসেন্ট জব সলুশন </h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1" id="home-services-area-three">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>১১-২০ তম গ্রেডের জব প্রস্তুতি বই </h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>পলিটেকনিক একাডেমিক বই </h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>ডিপ্লোমা জব প্রস্তুতি বই নন </h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>রিটেন জব প্রস্তুতি বই </h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
            <a href="{{route('home.coming-soon')}}">
              <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
                <div class="my-home-service-icon">
                  <img src="/web/img/it-books.png" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>ভাইভা সহায়িকা </h3>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- নিয়োগ বিজ্ঞপ্তি -->
  <section id="circular" class="our-service background-res background-ats py-5" style="background: #fff">
    <div class="container">
      <div class="row mb-5">
        <div class="title-area text-center">
          <h1 class="fw-bold custom-section-title">
            <span>নিয়োগ বিজ্ঞপ্তি</span>
          </h1>
        </div>
      </div>

      <div class="home-services-area">
        <div class="row" style="--bs-gutter-x: 0.3rem !important;">

          @foreach($circulars as $key => $value)

          <div class="col-md-3 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
            <a href="{{route('home.circular', $key)}}">
              <div class="my-home-service premium-course-box">
                <div class="my-home-service-icon">
                  <img src="/web/img/pdf-logo.jpg" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>{{$value}}</h3>
                </div>
              </div>
            </a>
          </div>

          @endforeach

          {{-- <div class="col-md-3 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
            <a href="{{route('home.circular')}}">
              <div class="my-home-service premium-course-box">
                <div class="my-home-service-icon">
                  <img src="/web/img/pdf-logo.jpg" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>ডিপ্লোমা জব সার্কুলার</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
            <a href="{{route('home.circular')}}">
              <div class="my-home-service premium-course-box">
                <div class="my-home-service-icon">
                  <img src="/web/img/pdf-logo.jpg" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>১১ - ২০ তম গ্রেডের জব সার্কুলার</h3>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-3 col-6 mb-1 dropdown bcs-jobs" id="home-services-area-one">
            <a href="{{route('home.circular')}}">
              <div class="my-home-service premium-course-box">
                <div class="my-home-service-icon">
                  <img src="/web/img/pdf-logo.jpg" alt="" loading="lazy">
                </div>
                <div class="my-home-service-content">
                  <h3>বে-সরকারি জব সার্কুলার</h3>
                </div>
              </div>
            </a>
          </div> --}}

        </div>
      </div>

    </div>
  </section>
  
<!-- আমাদের শিক্ষক মণ্ডলী -->
<section id="our_teacher" class="our_story background-res background-ats py-2" style="background: #eef">
  <div class="container">
    <div class="row py-5">
      <div class="title-area text-center">
        <h1 class="fw-bold custom-section-title">
          আমাদের <span>শিক্ষক মণ্ডলী </span>
        </h1>
      </div>
    </div>
    <div class="our-story-event-area col-md-6 offset-md-3 py-5">
      <div class="row g-3 g-lg-4">

        @foreach($teachers as $teacher)
          <a href="{{route('home.coming-soon')}}" class="col-6 col-lg-4 mb-5 text-decoration-none">
            <div class="event-area-content" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{$teacher->designation}}">
              <div class="event-icon">
                <img src="{{$teacher->image ? $teacher->image : '/img/avatar.png'}}" srcset="" alt="Teacher Image" style="height: 90px; border: 4px solid #1CAB55; border-radius: 50%;" loading="lazy">
              </div>
              <div class="event-content">
                <h3 class="text-uppercase text-black">{{$teacher->name}}</h3>
              </div>
            </div>
          </a>
        @endforeach

      </div>
    </div>
    <br>
  </div>
</section>

<section id="our-services" class="our-service background-res background-ats py-4" style="background-image: url('/web/img/Background_Noise.png'); background-color: rgba(0, 0, 0, 0.5);">
  <div class="container py-4">
    <div class="row">
      <div class="title-area text-center">
        <h2 class="fw-bold mb-4 custom-section-title">আমাদের সেবা সমূহ <br></h2>
      </div>
    </div>
    <div class="home-services-area">
      <div class="row" style="--bs-gutter-x: 0.3rem !important;">
        <div class="col-md-3 col-6 mb-2 dropdown bcs-jobs" id="home-services-area-one">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Diploma-in-Engineering Job Preparation</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-two" data-toggle="tooltip" data-placement="bottom" title="Tooltip on bottom">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Live MCQ Exam</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-three">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Written Exam</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Online Class</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Recoded Courses</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>11-20 Grade Job Preparation</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Live Chat</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Free Exam</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Free live Class</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Polytechnic Stady</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Duet Admission</h3>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-1" id="home-services-area-four">
          <a href="{{route('home.coming-soon')}}">
            <div class="my-home-service premium-course-box" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="...">
              <div class="my-home-service-icon">
                <img src="/web/img/it-books.png" alt="" loading="lazy">
              </div>
              <div class="my-home-service-content">
                <h3>Online Book</h3>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="our-success" class="our_story background-res background-ats py-2" style="background: #eef">
  <div class="container">
    <div class="row py-5">
      <div class="title-area text-center">
        <h1 class="fw-bold custom-section-title">
          আমাদের <span>সাফল্য</span>
        </h1>
      </div>
    </div>
    <div class="our-story-video-content-area py-4">
      <div class="row">
        <div class="col-md-6">
          <div class="our-story-content-area" style="max-height: 400px; overflow-y: auto;">
            {!! $about->text !!}
          </div>
        </div>
        <div class="col-12 d-block d-md-none py-4"></div>
        <div class="col-md-6">
          <div class="our-story-video-area mt-4">
            <div class="our-story" style="position: relative; width: 100%; height: 0; padding-bottom: 56.25%;">
              <iframe id="videoIframe2" src="https://www.youtube.com/embed/{{$about->video}}?si=myZrYsOTKstGK0zx" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
              <div onclick="showVideoModal('https://www.youtube.com/embed/{{$about->video}}?autoplay=1')" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
              </div>
            </div>
          </div>
        </div>
        <!-- Small devices space -->
      </div>
    </div>
    <br>
    </div>
  </div>
</section>

<!-- <section id="Home_odomiter" class="background-res background-ats py-5"
style="background-image: url('img/background-bg.jpg')">
<div class="container">
<div class="row g-3">
  <div class="col-6 col-lg-3">
    <div class="odomiter-content">
      <h3>200K+</h3>
      <p>Students</p>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="odomiter-content">
      <h3>150 +</h3>
      <p>Teachers</p>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="odomiter-content">
      <h3>100+</h3>
      <p>Staff</p>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="odomiter-content">
      <h3>30+</h3>
      <p>IT</p>
    </div>
  </div>
</div>
</div>
</section> -->

<section id="Student_review" class="background-res background-ats py-5"
style="background-image: url('/web/img/background-bg.jpg')">
<div class="container">
  <div class="row">
    <div class="title-area text-center">
      <h1 class="fw-bold mb-4 custom-section-title title-color-white">
        আমাদের শিক্ষার্থীদের <span>মতামত</span>
      </h1>
    </div>
  </div>
  <div class="student-review-area py-4">
    <div class="student-review-slider slider">

      @foreach($complains as $key => $complain)

      <div class="student-review">
        <div class="student-review-content">
          <div class="student-name mt-2">
            <h4>{{$complain->name}}</h4>
          </div>
          <div class="review">
            <p>{{$complain->details}}</p>
          </div>
        </div>
      </div>

      @endforeach
      
    </div>
  </div>
</div>
</section>
@endsection