@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Редактирование атрибута</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item"><a href="{{route('attribute.index')}}">Атрибуты</a></li>
						<li class="breadcrumb-item active" aria-current="page">Редактирование атрибута</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->
			<form action="{{route('attribute.update',['attribute'=>$attribute->slug])}}" method="POST">
				@csrf
				@method('PUT')
				<!--ROW OPENED-->
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div  class="card">
							<div class="card-header border-bottom">
								<h4 class="mb-0">Информация об атрибуте</h4>
							</div>
							<div class="card-body p-0 task-edit-main">
								<div class="row p-5 border-bottom">
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название рус</label>
											<input type="text" class="form-control text-dark @error('name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$attribute->name_ru}}" name="name_ru" placeholder="Введите русское название категории">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_ru') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Название каз</label>
											<input type="text" class="form-control text-dark @error('name') is-invalid @enderror" aria-describedby="validationServer03Feedback" value="{{$attribute->name_kz}}" name="name_kz" placeholder="Введите казахское название категории">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('name_kz') {{$message}} @enderror</div>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Порядок</label>
											<input type="number" class="form-control text-dark @error('order') is-invalid @enderror" name="order" value="{{$attribute->order}}" placeholder="Введите порядок в списке">
											<div id="validationServer03Feedback" class="invalid-feedback">@error('order') {{$message}} @enderror</div>
										</div>
									</div>
									<!-- <div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Отображение:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="otobrazhenie" id="client-salution1" data-placeholder="Choose Category..." tabindex="-1" aria-hidden="true">
												<option value="image" {{$attribute->otobrazhenie == "image" ? "selected" : ""}}>В виде картинок</option>
												<option value="text" {{$attribute->otobrazhenie == "text" ? "selected" : ""}}>В виде текста</option>							
											</select>
										</div>
									</div> -->
									<!-- <div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Чем является:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="type_attribut" id="client-salution1" data-placeholder="Choose Category..." tabindex="-1" aria-hidden="true">
												<option value="haracteristic" {{$attribute->type_attribut == "haracteristic" ? "selected" : ""}}>Характеристикой</option>
												<option value="variation" {{$attribute->type_attribut == "variation" ? "selected" : ""}}>Вариантом</option>					
											</select>
										</div>
									</div> -->
									
									<div class="col-sm-12 col-md-12 col-xl-6">
										<div class="form-group">
											<label class="form-label text-muted">Участвует в фильтрации:</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark" name="filter" id="client-salution1" data-placeholder="Choose Category..." tabindex="-1" aria-hidden="true">
												<option value="1" {{$attribute->filter == 1 ? "selected" : ""}}>Да</option>
												<option value="0" {{$attribute->filter == 0 ? "selected" : ""}}>Нет</option>					
											</select>
										</div>
									</div>
									<div class="col-sm-12 col-md-12 col-xl-4">
										<div class="form-group">
											<label class="form-label text-muted">Отоброжать в товаре?</label>
											<select class="form-control select2 form-select select2-hidden-accessible text-dark"  name="haracteristic" id="client-salution1" data-placeholder="Choose Category..." tabindex="-1" aria-hidden="true">
												<option value="0"  {{$attribute->haracteristic == "0" ? "selected" : ""}}>Нет</option>
												<option value="1" {{$attribute->haracteristic == "1" ? "selected" : ""}}>Да</option>					
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="row p-5">
								<div class="btn-list text-end">
									<a class="btn btn-outline-danger" href="{{route('attribute.index')}}"> <i class="fe fe-x-circle"></i> Отмена</a>
									<button class="btn btn-primary" type="submit"> <i class="fe fe-check-circle"></i> Изменить</button>
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
			