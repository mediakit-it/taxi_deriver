@extends('admin.master')

@section('content')
	<div class="side-app">
		<!-- CONTAINER -->
		<div class="main-container container-fluid">

			<!-- PAGE-HEADER -->
			<div class="page-header">
				<div>
					<h1 class="page-title">Tables</h1>
				</div>
				<div class="ms-auto pageheader-btn">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/">Главная</a></li>
						<li class="breadcrumb-item active" aria-current="page">Импорт</li>
					</ol>
				</div>
			</div>
			<!-- PAGE-HEADER END -->

			<!-- Row -->
			<div class="row row-sm">
				<div class="col-lg-12">
					<div class="card custom-card">
					<div class="row"  style="margin-bottom:20px; margin-top:20px; margin-left:10px; margin-right:10px;">
						<div class="col-md-6">
						<form action="{{route('importFile')}}" method="POST" enctype="multipart/form-data">
							@csrf
							<label for="div1" class="col-md-12 mb-2" style="text-align: center; font-weight: bold; font-size: 1.2rem;"> Импорт оборотки</label>
							<div class="d-flex gap-5" id="div1">
							<input class="form-control col-md-8" type="file" name="file" id="formFile">
							<button type="submit" class="btn btn-primary">Импортировать</button>
							</div>
						</form>
						</div>
						
					</div>
						<div class="card-header border-bottom">
							<h3 class="card-title">Таблица импортов</h3>
						</div>
						<div class="card-body">
							<!-- <p class="text-muted">Using the most basic table markup.</p> -->
							<div class="table-responsive">
								<table class="table  border text-nowrap text-md-nowrap">
									<thead>
										<tr>
											<th>ID</th>
											<th>Статус</th>
											<th>Ошибка</th>
										</tr>
									</thead>
									<tbody>
										@foreach($importProducts as $importProduct)
										<tr>
											<td>{{$importProduct->id}}</td>
											<td>{{$importProduct->status}}</td>
											<td>{{$importProduct->error}}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Row -->
		</div>
	</div>
@endsection