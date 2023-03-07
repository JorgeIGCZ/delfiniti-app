@extends('layouts.app')
@section('scripts')
    <script>
        $(function(){
        } );
    </script>
@endsection
@section('content')
    <style>

        /* Cool infinite background scrolling animation.
        * Twitter: @kootoopas
        */
        /* Exo thin font from Google. */
        @import url(https://fonts.googleapis.com/css?family=Exo:100);
        /* Background data (Original source: https://subtlepatterns.com/grid-me/) */
        /* Animations */
        @-webkit-keyframes bg-scrolling-reverse {
            100% {
                background-position: 50px 50px;
            }
            }
            @-moz-keyframes bg-scrolling-reverse {
            100% {
                background-position: 50px 50px;
            }
            }
            @-o-keyframes bg-scrolling-reverse {
            100% {
                background-position: 50px 50px;
            }
            }
            @keyframes bg-scrolling-reverse {
            100% {
                background-position: 50px 50px;
            }
            }
            @-webkit-keyframes bg-scrolling {
            0% {
                background-position: 50px 50px;
            }
            }
            @-moz-keyframes bg-scrolling {
            0% {
                background-position: 50px 50px;
            }
            }
            @-o-keyframes bg-scrolling {
            0% {
                background-position: 50px 50px;
            }
            }
            @keyframes bg-scrolling {
            0% {
                background-position: 50px 50px;
            }
        }
        /* Main styles */
        .az-content-dashboard {
            margin: 0;
            height: calc(100vh - 64px);
            color: #999;
            font: 400 16px/1.5 exo, ubuntu, "segoe ui", helvetica, arial, sans-serif;
            text-align: center;
            /* img size is 50x50 */
            background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAIAAACRXR/mAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAABnSURBVHja7M5RDYAwDEXRDgmvEocnlrQS2SwUFST9uEfBGWs9c97nbGtDcquqiKhOImLs/UpuzVzWEi1atGjRokWLFi1atGjRokWLFi1atGjRokWLFi1af7Ukz8xWp8z8AAAA//8DAJ4LoEAAlL1nAAAAAElFTkSuQmCC") repeat 0 0;
            -webkit-animation: bg-scrolling-reverse 4.2s infinite;
            /* Safari 4+ */
            -moz-animation: bg-scrolling-reverse 4.2s infinite;
            /* Fx 5+ */
            -o-animation: bg-scrolling-reverse 4.2s infinite;
            /* Opera 12+ */
            animation: bg-scrolling-reverse 4.2s infinite;
            /* IE 10+ */
            -webkit-animation-timing-function: linear;
            -moz-animation-timing-function: linear;
            -o-animation-timing-function: linear;
            animation-timing-function: linear;
        }
        .az-content-dashboard::before {
            content: "DELFINITI APP";
            font-size: 8rem;
            font-weight: 100;
            font-style: normal;
            position: relative;
            top: calc(50% - 100px);
        }
    </style>
   
@endsection
