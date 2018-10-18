/*Slightly updated version of mbostock's recep*/

function progressChart() {
    var margin = {top: 0, right: 0, bottom: 20, left: 0},
        width = 960,
        height = 500;

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

    var margin = {top: 0, right: 0, bottom: 20, left: 0},
        width = 960,
        height = 500,
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
        width = 960,
        height = 500;

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

