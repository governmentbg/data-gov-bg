// var opendata_colors = [ '#691212', '#c3a0a0']
var opendata_colors = [ '#0d90d1', '#a9dffa']


/*Slightly updated version of mbostock's recep*/

function progressChart() {
  var margin = {top: 0, right: 20, bottom: 10, left: 20},
      width = 960 - margin.left - margin.right,
      height = 50 - margin.top - margin.bottom;

  var x = d3.scale.linear();

  function chart(selection) {
    selection.each(function(data) {

      // Update the x-scale.
      x
        .domain([0, d3.max(data)])
        .range([0, width]);

      // Select the svg element, if it exists.
      var svg = d3.select(this).selectAll("svg").data([data]);

      svg.enter().append("svg").append("g").attr("class", "progressBar");
      svg .attr("width", width)
          .attr("height", height);

      // Update the bars.
      var progressBar = svg.select(".progressBar").selectAll("g").data(data);
      var progressEnter = progressBar.enter().append("g");

      progressEnter.append("rect")
        .attr("class", function(d, i){ return i ? "progress" : "total" })
        .attr("width", function(d){ return x(d) + "px" })
        .attr("height", 20)
        .attr("rx", 5)
        .attr("ry", 5)

      progressEnter.append("text")
        .attr("class", function(d, i){ return i ? "progress" : "total" })
        .attr("x", function(d,i){ return i ? width/6 : x(d) })
        .attr("y", 10)
        .attr("dx", -3)
        .attr("dy", ".35em")
        .attr("text-anchor", "end")
        .text(function(d){ return String(d)});

      progressBar.select("rect")
        .transition()
        .duration(1000)
        .attr("class", function(d, i){ return i ? "progress" : "total" })
        .attr("width", x)

      progressBar.select("text")
        .transition()
        .duration(1000)
        .attr("class", function(d, i){ return i ? "progress" : "total" })
        .text(function(d){ return String(d)});

      progressBar.exit().remove();

    });
  }

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  return chart;
}






function pieChart() {

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = 500 - margin.left - margin.right,
      height = 500 - margin.top - margin.bottom;
      radius = 250;

  var color = d3.scale.category20();

  var pie = d3.layout.pie()
    .value(function(d) { return d.count; })
    .sort(null);

  var formatPercentage = d3.format("0%");

  function chart(selection) {

    selection.each(function(data) {

      var arc = d3.svg.arc()
        .outerRadius(radius * 0.8);

      var outerArc = d3.svg.arc()
        .innerRadius(radius * 0.8)
        .outerRadius(radius * 0.8);


      // Compute pies
      var total = 0
      var _ = {};
      $.each(data, function(i){
        var val = data[i];
        _[val] = _[val]+1 || 1;
      });

      var pies = []
      for(o in _){
        total += _[o];
        pies.push({label: o, count: _[o]});
      }

      var key = function(d){ return d.data.label; };

      // Select the svg element, if it exists.
      var svg = d3.select(this).selectAll("svg").data([data]);

      // Otherwise, create the skeletal chart.
      var gEnter = svg.enter().append("svg");

      gEnter.append("g")
          .attr("class", "slices")
          .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
      gEnter.append("g")
          .attr("class", "labels")
          .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

      // Update the outer dimensions.
      svg .attr("width", width)
          .attr("height", height);

      var slice = svg.select(".slices").selectAll("path.slice")
        .data(pie(pies), key);

      slice.enter()
        .insert("path")
        .style("fill", function(d) { return color(d.data.label); })
        .attr("class", "slice");

      slice
        .transition().duration(1000)
        .attrTween("d", function(d) {
          this._current = this._current || d;
          var interpolate = d3.interpolate(this._current, d);
          this._current = interpolate(0);
          return function(t) {
            return arc(interpolate(t));
          };
        });

      slice.exit()
        .remove();


      var text = svg.select(".labels").selectAll("text")
        .data(pie(pies), key);

      text.enter()
        .append("text");

      function midAngle(d){
        return d.startAngle + (d.endAngle - d.startAngle)/2;
      }

      text.transition().duration(1000)
        .attrTween("transform", function(d) {
          this._current = this._current || d;
          var interpolate = d3.interpolate(this._current, d);
          this._current = interpolate(0);
          return function(t) {
            var d2 = interpolate(t);
            var pos = outerArc.centroid(d2);
            pos[0] = radius * 0.5 * (midAngle(d2) < Math.PI ? 1 : -1);
            return "translate("+ pos +")";
          };
        })
        .text(function(d) {
          return d.data.label + ' ' + formatPercentage(d.data.count/total); })
        .style("text-anchor", 'middle');

      text.exit()
        .remove();

    });
  }

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  chart.radius = function(_) {
    if (!arguments.length) return radius;
    radius = _;
    return chart;
  };

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };

  return chart;
}







function histogramChart() {
  var margin = {top: 0, right: 0, bottom: 20, left: 0},
      width = 960 - margin.left - margin.right,
      height = 500 - margin.top - margin.bottom;

  var histogram = d3.layout.histogram(),
      x = d3.scale.ordinal(),
      y = d3.scale.linear(),
      xAxis = d3.svg.axis().scale(x).orient("bottom").tickSize(3, 0);

  var formatCount = d3.format(",.0f");

  function chart(selection) {
    selection.each(function(data) {

      // Compute the histogram.
      data = histogram(data);

      // Update the x-scale.
      x   .domain(data.map(function(d) { return d.x; }))
          .rangeRoundBands([0, width - margin.left - margin.right], .1);

      // Update the y-scale.
      y
        .domain([0, d3.max(data, function(d) { return d.y; })])
        .range([height - margin.top - margin.bottom, 0]);


      // Select the svg element, if it exists.
      var svg = d3.select(this).selectAll("svg").data([data]);

      // Otherwise, create the skeletal chart.
      var gEnter = svg.enter().append("svg").append("g");
      gEnter.append("g").attr("class", "bars");
      gEnter.append("g").attr("class", "x axis");

      // Update the outer dimensions.
      svg .attr("width", width)
          .attr("height", height);

      // Update the inner dimensions.
      var g = svg.select("g")
          .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      // Update the bars.
      var bar = svg.select(".bars").selectAll("g").data(data);
      var barEnter = bar.enter().append("g")
         .attr("class", "bar")
         .attr("transform", function(d) { return "translate(" + x(d.x)  + ",0)"; });

      barEnter.append("rect")
        .attr("width", x.rangeBand())
        .attr("x", 1)
        .attr("y", function(d) { return y(d.y); })
        .attr("height", function(d) { return  y.range()[0]-y(d.y); })

      barEnter.append("text")
        .attr("dy", "1.5em")
        .attr("y", function(d) { return y(d.y); })
        .attr("x", x.rangeBand()/2)
        .attr("text-anchor", "middle")
        .text(function(d) { return formatCount(d.y); });

      bar.select("rect")
        .transition()
        .duration(1000)
        .attr("y", function(d) { return y(d.y); })
        .attr("height", function(d) { return y.range()[0]-y(d.y); })

      bar.select("text")
         .transition()
        .duration(1000)
        .attr("y", function(d) { return y(d.y); })
        .text(function(d) { return formatCount(d.y); });

      bar.exit().remove();

      // Update the x-axis.
      g.select(".x.axis")
          .attr("transform", "translate(0," + y.range()[0] + ")")
          .call(xAxis);
    });
  }

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  // Expose the histogram's value, range and bins method.
  d3.rebind(chart, histogram, "value", "range", "bins");

  // Expose the x-axis' tickFormat method.
  d3.rebind(chart, xAxis, "tickFormat");

  return chart;
}


function sortedBarChart(){

  var margin = {top: 20, right: 20, bottom: 100  , left: 20},
        width = 400,
        height = 300;

  var x = d3.scale.ordinal(),
      y = d3.scale.linear(),
      xAxis = d3.svg.axis().scale(x).orient("bottom").tickSize(1, 0);

  var formatType = ",.0f",
      formatFactor = 1,
      formatCount;

  var data = [];
  var highlight = '',
      highlightColor = '#f0f059';

  var sortData=false,
      doSort;

  var updateData;

  function chart(selection){
    selection.each(function() {


      // Convert dispatched object to convenience array
      // TO DO ADD FLAG AND ABSTRACTION FUNCTION
      base = []
      values = []
      fullData = []
      for (o in data){
        base.push(o)
        values.push(data[o])
        fullData.push([o, data[o]])
      }

      formatCount = d3.format(formatType)

      if(formatType.search('%') > -1){ formatFactor=100;}

      // Update axises domain
      x.domain(base).rangeRoundBands([0, width - margin.left - margin.right], .1);
      y.domain([d3.min(values), d3.max(values)]).range([height - margin.top - margin.bottom, 0]);

      // Select the svg element, if it exists.
      var svg = d3.select(this).selectAll("svg").data([base]);

      // Otherwise, create the skeletal chart.
      var gEnter = svg.enter().append("svg").append("g").attr("class", "sortedBars");

      // Update the outer dimensions.
      svg .attr("width", width)
          .attr("height", height);

      gEnter.append("g").attr("class", "bars");
      gEnter.append("g").attr("class", "x axis");

      // Update the inner dimensions.
      var g = svg.select("g")
          .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      // Update the bars.
      var bar = svg.select(".bars").selectAll("g").data(base);
      var barEnter = bar.enter().append("g")
         .attr("class", "bar")
         .attr("transform", function(d) { return "translate(" + x(d)  + ",0)"; });

      barEnter.append("rect")
        .attr("width", x.rangeBand())
        .attr("x", 0)
        .attr("y", function(d) { return isNaN(y(d[1])) ? 1 : y(d[1]); })
        .attr("height", function(d) { return isNaN(y(d[1])) ? height : y.range()[0]-y(d[1]); })
        .style('fill', function(v) { return highlight == v[0] ? highlightColor : '' });

      barEnter.append("text")
        .attr("dy", ".5em")
        .attr("y", function(d) { return isNaN(y(d[1])) ? 1 : y(d[1]); })
        .attr("x", x.rangeBand()/2)
        .attr("text-anchor", "middle")
        .attr("transform", function(d) {
          return "rotate(-90 " + x.rangeBand()/2 + ", " + (isNaN(y(d[1])) ? 1 : y(d[1])) + " )" })
        .text(function(d) { return isNaN(formatCount(d[1])) ? "1" : formatCount(d[1]/formatFactor); });

      g.select(".x.axis")
          .attr("transform", "translate(0," + y.range()[0] + ")")
          .call(xAxis)
          .selectAll("text")
            .style("text-anchor", "end")
            .attr("transform", "translate(" + (-x.rangeBand()/2) + "," + (x.rangeBand()) + ")rotate(-90)");

      doSort = function(){

        chartData = []
        bar.each(function(d){
          chartData.push(d)
        })

         // Copy-on-write since tweens are evaluated after a delay.
        var xs = x.domain(chartData.sort(sortData
          ? function(a, b) { return b[1] - a[1]; }
          : function(a, b) { return base.indexOf(a[0])-base.indexOf(b[0]); })
          .map(function(d) { return d[0]; }))
          .copy();

        svg.selectAll(".bar")
          .sort(function(a, b) { return xs(a) - xs(b); });

        var transition = svg.transition().duration(750);
          // delay = function(d, i) { return i * 50; };

        transition.selectAll(".bar")
          // .delay(delay)
          .attr("transform", function(d) { return "translate(" + xs(d[0])  + ",0)"; });

        transition.select(".x.axis")
          .call(xAxis)
          .selectAll("text")
          .style("text-anchor", "end")
          .attr("transform", "translate(" + (-x.rangeBand()/2) + "," + (x.rangeBand()) + ")rotate(-90)");

      }


      updateData = function() {


        // Convert object dispatched object to convenience array
        base = []
        values = []
        fullData = []
        for (o in data){
          base.push(o)
          values.push(data[o])
          fullData.push([o, data[o]])
        }

        var adjMin = ( d3.min(values) < 0 ? d3.min(values)*1.2 : d3.min(values)*0.8 )
        var adjMax = ( d3.max(values) < 0 ? d3.max(values)*0.8 : d3.max(values)*1.2 )

        // reset scale
        y.domain([adjMin, adjMax])

        // update data
        bar.data(fullData).transition().duration(1000)

        bar.selectAll('rect').data(function(v) { return [v] })
          .transition().duration(1000)
          .attr("y", function(v) { return y(v[1]); })
          .attr("height", function(v) { return  y.range()[0]-y(v[1]); });

        bar.selectAll("text").data(function(v) { return [v] })
          .transition().duration(1000)
          .attr("dy", ".5em")
          .attr("y", function(d) { return y(d[1]); })
          .attr("x", x.rangeBand()/2)
          .attr("transform", function(d) { return "rotate(-90 " + x.rangeBand()/2 + ","+ y(d[1]) + ")"; } )
          .text(function(d) { return formatCount(d[1]/formatFactor); });

      };

    })
  };


  chart.data = function(_) {
      if (!arguments.length) return data;
      data = _;
      if (typeof updateData === 'function') updateData();
      return chart;
  };


  chart.sortData = function(_) {
      if (!arguments.length) return sortData;
      sortData = _;
      if (typeof doSort === 'function') doSort();
      return chart;
  };

  chart.highlight = function(_) {
    if (!arguments.length) return highlight;
    highlight = _;
    return chart;
  };

  chart.formatType = function(_) {
    if (!arguments.length) return formatType;
    formatType = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _ ;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  return chart

}


// Expects data as a array of 2 element arrays [[key, value],[key, value],...]

function dualLineChart() {

  var margin = {top: 30, right: 50, bottom: 10  , left: 50},
        width = 400 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;

  var x = d3.scale.ordinal(),
      y0 = d3.scale.linear()
      y1 = d3.scale.linear()

  var nicer = 2;

  var xAxis = d3.svg.axis().scale(x).orient("bottom").ticks(5),
      yAxisLeft = d3.svg.axis().scale(y0).orient("left").ticks(5),
      yAxisRight = d3.svg.axis().scale(y1).orient("right").ticks(5);

  var yAxisLeftText = 'Престъпност на хил',
      yAxisRightText = 'Разкриваемост процент';

  var valueline1 = d3.svg.line()
      .interpolate('cardinal')
      .x(function(d) { return x(d[0]) + x.rangeBand() / 2; })
      .y(function(d) { return y0(+d[1]); });

  var valueline2 = d3.svg.line()
      .interpolate('cardinal')
      .x(function(d) { return x(d[0]) + x.rangeBand() / 2; })
      .y(function(d) { return y1(+d[2]); });

  var r = 5;
  var color = d3.scale.ordinal().range(opendata_colors);

  var formatLine1 = d3.format(",.0f"),
      formatLine2 = d3.format("%");;

  var data = []
  var updateData;

  function chart(selection) {
    selection.each(function() {


      // Convert object dispatched object to convenience array
      base = []
      values0 = []
      values1 = []
      processedData = []
      for (o in data[0]){
        base.push(o)
        values0.push(data[0][o])
        values1.push(data[1][o])
        processedData.push([o, data[0][o], data[1][o]])
      }

      // Update the x-scale.
      x.domain(base).rangeRoundPoints([0, width - margin.left - margin.right]);

      // Update the left y-scale.
      y0
        .domain([d3.min(values0) , d3.max(values0)])
        .nice(nicer)
        .range([height - margin.top - margin.bottom, 0]);

      // Update the right y-scale.
      y1
        .domain([d3.min(values1), d3.max(values1)])
        .nice(nicer)
        .range([height - margin.top - margin.bottom, 0]);


      // Select the svg element, if it exists.
      var svg = d3.select(this).selectAll("svg").data([processedData]);

      // Otherwise, create the skeletal chart.
      var gLines = svg
        .enter()
        .append("svg")
          .attr("width", width + margin.left + margin.right)
          .attr("height", height + margin.top + margin.bottom)
          .attr("class", "dualLineChart")
        .append("g")
          .attr("transform",
              "translate(" + (margin.left) + "," +  margin.top + ")");

      svg.append("g")
          .attr("class", "x-axis")
          .attr("transform", "translate(" + (margin.left) + "," + (height) + ")")
          // .attr("transform", "translate(0," + (height - margin.top - margin.bottom) + ")")
          .call(xAxis);

      svg.selectAll(".x-axis text")
          .attr("transform", function(d) {
             return "translate(" + this.getBBox().height*-2 + "," + this.getBBox().height + ")rotate(-45)";
         });

      svg.append("g")
          .attr("class", "y-axis-left")
          .style("fill", "steelblue")
          .attr("transform", "translate(" + (margin.left) + " ," + (margin.top + margin.bottom) + ")")
          .call(yAxisLeft)
          .append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", "1em")
            .style("text-anchor", "end")
            .text(yAxisLeftText);

      svg.append("g")
          .attr("class", "y-axis-right")
          .attr("transform", "translate(" + (width - margin.right) + " ," + (margin.top + margin.bottom) + ")")
          .style("fill", "red")
          .call(d3.svg.axis().scale(y1).orient("right").ticks(5))
          // .call(yAxisRight); BUG
          .append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", "-1em")
            .style("text-anchor", "end")
            .text(yAxisRightText);

      // initiate lines.
      var line1 = gLines.append("path")
          .attr('class','line1')
          .style("stroke", function(d) { return color(1); })
          .attr("d", ( isNaN(valueline1(processedData)) ? null : valueline1(processedData) ) ) ;

      var line2 = gLines.append("path")
          .attr('class','line2')
          .style("stroke", function(d) { return color(2); })
          .attr("d", ( isNaN(valueline2(processedData)) ? null : valueline2(processedData) ));

      // initiate points
      var points1 = gLines.append("g")
        .attr("class", "line-points1");

      points1.selectAll('circle')
        .data(function(d){ return d })
        .enter().append('circle')
        .attr("cx", function(d) { return isNaN(x(+d[0])) ? null : x(+d[0]) + x.rangeBand() / 2; })
        .attr("cy", function(d) { return isNaN(y0(d[1])) ? null : y0(d[1]); })
        .attr("r", r)
        .style("fill", function(d) { return color(1); });

      var points2 = gLines.append("g")
        .attr("class", "line-points2");

      points2.selectAll('circle')
        .data(function(d){ return d })
        .enter().append('circle')
        .attr("cx", function(d) { return isNaN(x(+d[0])) ? null : x(+d[0]) + x.rangeBand() / 2; })
        .attr("cy", function(d) { return isNaN(y1(d[2])) ? null : y1(d[2]); })
        .attr("r", r)
        .style("fill", function(d) { return color(2); });

      // initiate values
      var vals1 = gLines.append('g')
          .attr('class','line-values1');

      vals1.selectAll('text')
          .data(function(d){ return d})
          .enter().append('text')
          .attr("x", function(d) { return isNaN(x(+d[0])) ? null : x(+d[0]) + x.rangeBand() / 2; })
          .attr("y", function(d) { return isNaN(y0(d[1])) ? null : y0(d[1]); })
          .attr('dy', -10)
          .attr("text-anchor", "middle")
          .text(function(d) { return  isNaN(formatLine1(d[1]).replace(",","")) ? formatLine1(0) : formatLine1(d[1]); });

      var vals2 = gLines.append('g')
          .attr('class','line-values2');

      vals2.selectAll('text')
          .data(function(d){ return d})
          .enter().append('text')
          .attr("x", function(d) { return isNaN(x(+d[0])) ? null : x(+d[0]) + x.rangeBand() / 2; })
          .attr("y", function(d) { return isNaN(y1(d[2])) ? null : y1(d[2]); })
          .attr('dy', -10)
          .attr("text-anchor", "middle")
          .text(function(d) { return isNaN(formatLine2(d[2]/100).replace(",","")) ? formatLine2(0) : formatLine2(d[2]/100); });




      updateData = function() {

        // Convert object dispatched object to convenience array
        base = []
        values0 = []
        values1 = []
        processedData = []
        for (o in data[0]){
          base.push(o)
          values0.push(data[0][o])
          values1.push(data[1][o])
          processedData.push([o, data[0][o], data[1][o]])
        }


        // reset scales
        x.domain(base);
        y0.domain([d3.min(values0), d3.max(values0)]).nice(nicer);
        y1.domain([d3.min(values1), d3.max(values1)]).nice(nicer);


        // update data
        svg.select("g.x.axis")
          .transition()
          .duration(750)
          .call(xAxis);
        svg.select("g.y-axis-left")
          .transition()
          .duration(750)
          .call(yAxisLeft);
        svg.select("g.y-axis-right")
          .transition()
          .duration(750)
          .call(d3.svg.axis().scale(y1).orient("right").ticks(5));
          // .call(yAxisRight); BUG


        // update data
        svg.select(".line1")
            .transition()
            .duration(1000)
            .attr("d", valueline1(processedData));

        svg.select(".line2")
          .transition()
          .duration(1000)
          .attr("d", valueline2(processedData));

        // update data
        var linePoints1 = svg.selectAll(".line-points1").selectAll('circle').data(processedData).transition().duration(1000)
        var linePoints2 = svg.selectAll(".line-points2").selectAll('circle').data(processedData).transition().duration(1000)
        var lineText1 = svg.selectAll(".line-values1").selectAll('text').data(processedData).transition().duration(1000)
        var lineText2 = svg.selectAll(".line-values2").selectAll('text').data(processedData).transition().duration(1000)

        linePoints1
          .attr("cx", function(d) { return x(+d[0])+ x.rangeBand() / 2; })
          .attr("cy", function(d) { return y0(d[1]); });

        linePoints2
          .attr("cx", function(d) { return x(+d[0]) + x.rangeBand() / 2; })
          .attr("cy", function(d) { return y1(d[2]); });

        lineText1
          .attr("x", function(d) { return x(+d[0]) + x.rangeBand() / 2;})
          .attr("y", function(d) { return y0(d[1]); })
          .text(function(d) { return formatLine1(d[1]); }) ;

        lineText2
          .attr("x", function(d) { return x(+d[0]) + x.rangeBand() / 2;})
          .attr("y", function(d) { return y1(d[2]); })
          .text(function(d) { return formatLine2(d[2]/100); });


      };

    });
  }



  chart.data = function(value) {
      if (!arguments.length) return data;
      data = value;
      if (typeof updateData === 'function') updateData();
      return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _ ;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  // Expose the x-axis' tickFormat method.
  d3.rebind(chart, xAxis, "tickFormat");

  return chart;
}




function multiLineChart() {

  var margin = {top: 30, right: 50, bottom: 40, left: 60},
        width = 400 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;

  var x = d3.scale.ordinal(),
      y = d3.scale.linear()

  var nicer = 5;

  var xAxis = d3.svg.axis().scale(x).orient("bottom").ticks(5);
  var yAxis = d3.svg.axis().scale(y).orient("left").ticks(5);
  var yAxisText = 'Процентна промяна';

  var mline = d3.svg.line()
      .interpolate('cardinal')
      .x(function(d) { return x(d.base) + x.rangeBand() / 2; })
      .y(function(d) { return y(d.value); });

  var r = 5;
  var color = d3.scale.ordinal().range(opendata_colors);

  var formatCount = d3.format("%");


  var data = []
  var updateData;

  function chart(selection) {
    selection.each(function() {

      $(this).empty()

      width = $(this).width() - margin.left - margin.right,
      // height = $(this).height() - margin.top - margin.bottom;

      x.rangeRoundBands([0, width]);
      y.range([height, 0]);

      var svg = d3.select(this).append("svg")
          .attr("width", width + margin.left + margin.right)
          .attr("height", height + margin.top + margin.bottom)
          .attr('class','multiLineChart')
        .append("g")
          .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      color.domain(d3.keys(data));

      // Convert object dispatched object to convenience object

      var series = d3.keys(data).map(function(key) {

        return {
          key: key,
          values: d3.keys(data[key]).map(function(o) {
            return {base: o, value: data[key][o]};
          })

        };
      });

      x.domain(series[0].values.map(function(d) { return d.base } ));
      y.domain([
        d3.min(series, function(d) { return d3.min(d.values, function(v) { return v.value; }) }),
        d3.max(series, function(d) { return d3.max(d.values, function(v) { return v.value; }) })
      ]).nice(nicer);

      svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis)

      svg.selectAll(".x.axis text")
          .attr("transform", function(d) {
             return "translate(" + this.getBBox().height*-2 + "," + this.getBBox().height + ")rotate(-45)";
         });

      svg.append("g")
          .attr("class", "y axis")
          .call(yAxis)
        .append("text")
          .attr("transform", "rotate(-90)")
          .attr("y", 6)
          .attr("dy", ".71em")
          .style("text-anchor", "end")
          .text(yAxisText);


      var lineGroup = svg.selectAll(".line-group")
          .data(series)
        .enter().append("g")
          .attr("class", "line-group");

      lineGroup.append("path")
          .attr("class", "mline")
          .attr("d", function(d) { return mline(d.values); })
          .style("stroke", function(d,i) { return color(d.key); });

      var linePoints = lineGroup.append("g")
          .attr("class", "line-points");

      linePoints.selectAll('circle')
          .data(function(d,i){ return d.values; })
          .enter().append('circle')
          .attr("cx", function(d, i) { return x(d.base) + x.rangeBand() / 2; })
          .attr("cy", function(d, i) { return y(d.value); })
          .attr("r", 5)
          .style("fill", function(d,i) { return color(this.parentElement.__data__.key)} );

      var lineValues = lineGroup.append('g')
          .attr('class','line-values');

      lineValues.selectAll('text')
          .data(function(d,i) { return d.values; })
          .enter().append('text')
          .attr("x", function(d, i) { return x(d.base) + x.rangeBand() / 2; })
          .attr("y", function(d, i) { return y(d.value) })
          .attr('dy', -10)
          .attr("text-anchor", "middle")
          .text(function(d) { return formatCount(d.value/100); });

      lineGroup.append("text")
        .datum(function(d) { return d; })
        .attr('class','line-names')
        .attr("transform", function(d) {
          return "translate(" + x(d.values[d.values.length-1].base) + "," + y(d.values[d.values.length-1].value) + ")rotate(-50)"; })
        .attr("x", x.rangeBand())
        .attr("dy", ".35em")
        .text(function(d) { return d.key; });


      updateData = function() {

       // Convert object dispatched object to convenience array

        var series = d3.keys(data).map(function(key) {

          return {
            key: key,
            values: d3.keys(data[key]).map(function(o) {
              return {base: o, value: data[key][o]};
            })

          };
        });

        x
          .domain(series[0].values.map(function(d) { return d.base } ));
          // .rangeRoundBands([0, width]);
        y
          // .range([0, height]);
          .domain([
            d3.min(series, function(d) { return d3.min(d.values, function(v) { return v.value; }) }),
            d3.max(series, function(d) { return d3.max(d.values, function(v) { return v.value; }) })
          ]).nice(nicer);


        // update axis
        svg.select("g.x.axis")
          .transition()
          .duration(750)
          .call(xAxis);
        svg.select("g.y.axis")
          .transition()
          .duration(750)
          .call(yAxis);

        // update data
        var lineGroup = svg.selectAll(".line-group")
          .data(series);

        var linePoints = lineGroup.selectAll(".line-points")
        var lineValues = lineGroup.select(".line-values")
        var lineNames = lineGroup.selectAll('.line-names')


        linePoints.each(function(s){
          d3.select(this).datum(function(d){
            return d3.select(this.parentNode).datum();
          })
        });

        var lineCircles = linePoints.selectAll('circle').transition().duration(1000)

        lineCircles.each(function(s){
          d3.select(this).datum(function(d){
            var currentKey = d3.select(this).datum()['base']
            var newValues = d3.select(this.parentNode).datum()['values'];
            var currentVal = newValues.filter(function(v){ return v.base==currentKey })
            return currentVal[0];
          })
        });

        lineValues.each(function(s){
          d3.select(this).datum(function(d){
            return d3.select(this.parentNode).datum();
          })
        });

        var lineText = lineValues.selectAll('text').transition().duration(1000)

        lineText.each(function(s){
          d3.select(this).datum(function(d){
            var currentKey = d3.select(this).datum()['base']
            var newValues = d3.select(this.parentNode).datum()['values'];
            var currentVal = newValues.filter(function(v){ return v.base==currentKey })
            return currentVal[0];
          })
        });

        lineNames.each(function(s){
          d3.select(this).datum(function(d){
            return d3.select(this.parentNode).datum();
          })
        })


        lineGroup.select('path').transition().duration(1000)
          .attr("d", function(d,i) {return mline(d.values); });

        lineCircles
          .attr("cy", function(d) { return y(d.value) });

        lineText
          .attr("y", function(d) { return y(d.value) })
          .text(function(d) { return formatCount(d.value/100); });

        lineNames.transition().duration(1000)
          .attr("transform", function(d) {
            return "translate(" + x(d.values[d.values.length-1].base) + "," + y(d.values[d.values.length-1].value) + ")rotate(-50)"; })
          .text(function(d) { return d.key; });

      };

    });

  };



  chart.data = function(value) {
      if (!arguments.length) return data;
      data = value;
      if (typeof updateData === 'function') updateData();
      return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _ ;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  // Expose the x-axis' tickFormat method.
  d3.rebind(chart, xAxis, "tickFormat");

  return chart;
}


