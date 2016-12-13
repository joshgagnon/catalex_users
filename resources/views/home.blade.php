@extends('app')

@section('content')





                    <div class="services container">

                     <div class="row">
                        <div class="col-md-6">
                            <div class="service">
                            <a href="{{ route('browser-login') }}">
                                 <i class="fa fa-search"></i>
                                <h4>Law Browser</h4>
                                <p>
                                    Find law faster and easier than ever before.  Definition
                                    recognition, automatic cross-references, side-by-side
                                    browsing, and more!
                                </p>
                                 </a>
                            </div>
                        </div>




                        <div class="col-md-6">
                            <div class="service">
                            @if ($user->hasGoodCompaniesAccess())
                            <a href="{{ route('good-companies-login') }}">
                                <i class="fa fa-briefcase"></i>
                                <h4>Good Companies</h4>
                                <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                            </a>
                            @else
                            <a href="{{ route('user-services.index', array(urlencode('Good Companies') => 1)) }}" class="disabled-service">
                                <i class="fa fa-briefcase"></i>
                                <h4>Good Companies</h4>
                                <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                                <p>Click here to for features and pricing.</p>
                            </a>
                            @endif
                            </div>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="service">
                            <a href="http://workingdays.catalex.nz/">
                                 <i class="fa fa-calendar"></i>
                                <h4>Working Days</h4>
                                <p>
                                    Calculates legal deadlines based on common definitions of “working day”. 
                                </p>
                                 </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="service">
                            <a href="https://concat.catalex.nz/">
                                 <i class="fa fa-copy"></i>
                                <h4>ConCat</h4>
                                <p>
                                    Concatenates (combines) PDF documents quickly and securely.
                                </p>
                                </a>
                            </div>
                        </div>

                    </div>

                    </div>

@endsection
