@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Редактирование акции</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('category.index')}}">Акции</a></li>
						<li class="breadcrumb-item active" aria-current="page">Редактирование акции</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('discount.update',['discount'=>$discount->slug])}}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация о категории</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Изменить картинку акции</label>
										<input type="file" name="file" class="dropify" data-height="200" @if($discount->image !== null) data-default-file="{{ $discount->image }}" @endif />
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Название русское</label>
											<input type="text" class="form-control text-dark @error('name_ru') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$discount->name_ru}}" name="name_ru" placeholder="Введите русское название акции">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Название казахское</label>
											<input type="text" class="form-control text-dark @error('name_kz') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$discount->name_kz}}" name="name_kz" placeholder="Введите казахское название акции">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Порядок</label>
											<input type="number" class="form-control text-dark @error('order') is-invalid @enderror" name="order" value="{{$discount->order}}" placeholder="Введите порядок в списке">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('order') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание рус</label>
										<textarea class="form-control @error('description_ru') is-invalid @enderror" id="validationTextarea" name="description_ru" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$discount->description_ru}}</textarea>								
										<div class="invalid-feedback">@error('description_ru') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<label class="form-label text-muted">Описание каз</label>
										<textarea class="form-control @error('description_kz') is-invalid @enderror" id="validationTextarea" name="description_kz" style="width: 100%;height: 200px;border: 1px solid #eee;border-radius: 3px;padding: 15px;">{{$discount->description_kz}}</textarea>								
										<div class="invalid-feedback">@error('description_kz') {{$message}} @enderror</div>
									</div>

									<div class="col-sm-12 col-md-12 col-xl-12">
										<div class="form-group">
											<label class="form-label text-muted">Страна в которой отображать:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" id="selectCountry" name="country" data-placeholder="Выберите категорию" tabindex="-1" aria-hidden="true">
													<option value="Беларусь" {{$discount->country=="Беларусь" ? "selected" : ""}}>Беларусь</option>
													<option value="Россия" {{$discount->country=="Россия" ? "selected" : ""}}>Россия</option>
													<option value="Казахстан" {{$discount->country=="Казахстан" ? "selected" : ""}}>Казахстан</option>
											</select>
										</div>
									</div>
								</div>
								<div class="row p-5">
									<div class="btn-list text-end">
										<a class="btn btn-outline-danger" href="{{route('discount.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
										<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Изменить</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--ROW CLOSED-->
			</form>
		</div>
	</div>
@endsection

@section('scripts')
	<!--Internal Fileuploads js-->
	<script src="{{asset('assets/plugins/fileuploads/js/fileupload.js')}}"></script>
	<script src="{{asset('assets/plugins/fileuploads/js/file-upload.js')}}"></script>

	<!--Internal Fancy uploader js-->
	<script src="{{asset('assets/plugins/fancyuploder/jquery.ui.widget.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/jquery.fileupload.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/jquery.iframe-transport.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/jquery.fancy-fileupload.js')}}"></script>
	<script src="{{asset('assets/plugins/fancyuploder/fancy-uploader.js')}}"></script>


@endsection
			