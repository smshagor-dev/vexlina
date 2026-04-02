import 'package:active_ecommerce_seller_app/data_model/chart_response.dart';
import 'package:active_ecommerce_seller_app/my_theme.dart';
import 'package:active_ecommerce_seller_app/repositories/shop_repository.dart';
import 'package:flutter/material.dart';
import 'package:syncfusion_flutter_charts/charts.dart';

class MChart extends StatefulWidget {
  const MChart({super.key});

  @override
  State<MChart> createState() => _MChartState();
}

class _MChartState extends State<MChart> {
  List<ChartResponse> chartList = [];

  List<CartesianSeries<OrdinalSales, String>> _createSampleData() {
    final data = List.generate(chartList.length, (index) {
      return OrdinalSales(
        chartList[index].date,
        int.parse(chartList[index].total!.round().toString()),
      );
    });

    return [
      StackedColumnSeries<OrdinalSales, String>(
        dataSource: data,
        xValueMapper: (OrdinalSales sales, _) => sales.year,
        yValueMapper: (OrdinalSales sales, _) => sales.sales,
        color: MyTheme.app_accent_color,
        enableTooltip: true,
      ),
    ];
  }

  getChart() async {
    var response = await ShopRepository().chartRequest();
    chartList.addAll(response.values);
    setState(() {});
  }

  @override
  void initState() {
    getChart();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Center(
        child: SfCartesianChart(
      primaryXAxis: CategoryAxis(),
      //title: ChartTitle(text: 'Flutter Chart'),
      legend: Legend(isVisible: true),
      series: _createSampleData(),
      // tooltipBehavior: _tooltipBehavior,
    ));
  }
}

class OrdinalSales {
  final String? year;
  final int sales;

  OrdinalSales(this.year, this.sales);
}
