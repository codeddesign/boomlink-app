$(function(){
  App = Ember.Application.create();
  
  App.ApplicationController = Ember.ArrayController.extend({
    content: Ember.A(CHART_DATA)
  });
  
  App.BarChartComponent = Ember.Component.extend({
    tagName: 'svg',
    attributeBindings: 'width height'.w(),
    margin: {top: 20, right: 15, bottom: 30, left: 20},
    
    w: function(){
      return this.get('width') - this.get('margin.left') - this.get('margin.right');
    }.property('width'),
  
    h: function(){
      return this.get('height') - this.get('margin.top') - this.get('margin.bottom');
    }.property('height'),  
  
    transformG: function(){
      return "translate(" + this.get('margin.left') + "," + this.get('margin.top') + ")";
    }.property(),
      
    transformX: function(){
      return "translate(0,"+ this.get('h') +")";
    }.property('h'),   
  
    draw: function(){
      var formatPercent = d3.format("0");
      var width = this.get('w');
      var height = this.get('h');
      var data = this.get('data');
      var svg = d3.select('#'+this.get('elementId'));
      var x = d3.scale.ordinal().rangeRoundBands([0, width], 0.1);
      var y = d3.scale.linear().range([height, 0]);
      var xAxis = d3.svg.axis().scale(x).orient("bottom");
      var yAxis = d3.svg.axis().scale(y).orient("left").ticks(5).tickFormat(formatPercent);
      
	  var formatTime = d3.time.format("");
	  var div = d3.select("body").append("div")   
    			  .attr("class", "tooltip")               
    			  .style("opacity", 0);
	  
	  
      x.domain(data.map(function(d) { return d.letter; }));
      y.domain([0, d3.max(data, function(d) { return d.frequency; })]);
  
      //svg.select(".axis.x").call(xAxis);
      svg.select(".axis.y").call(yAxis);
	  
	  svg.selectAll("line")
	  .attr("x2","650");
	  
	  svg.selectAll("text")
	  .attr("style","display:none;");
	  
	  svg.selectAll("path")
	  .attr("style","display:none;");
  
      svg.select(".rects").selectAll("rect")
        .data(data)
      .enter().append("rect")
        .attr("class", "bar")
        .attr("x", function(d) { return x(d.letter); })
        .attr("width", x.rangeBand())
        .attr("y", function(d) { return y(d.frequency); })
        .attr("height", function(d) { return height - y(d.frequency); })
		.on("mouseover", function(d) {      
            div.transition()        
                .duration(200)      
                .style("opacity", .9);      
            div .html((d.letter) + ", "  + d.frequency)  
                .style("left", (d3.event.pageX) + "px")     
                .style("top", (d3.event.pageY - 28) + "px");    
            })                  
        .on("mouseout", function(d) {       
            div.transition()        
                .duration(500)      
                .style("opacity", 0);   
        });
    },
  
    didInsertElement: function(){
      this.draw();
    }
  });
});


 /*var newContent = [];
var max = 100;
var curr_date = new Date();
var hour = curr_date.getHours();
var minute = curr_date.getMinutes();
var second = curr_date.getSeconds(); 
     
       for(var i = 0, l = 60; i < l; i++) {
            if(i==0)
			{
				var item = Ember.Object.create({
                letter: hour+':'+i
                ,frequency: '0.03'
            	});
			}
			if(i>0 && i<10)
			{
				var item = Ember.Object.create({
                letter: hour+':0'+i
                ,frequency: '0.10'
            	});
			}
			else if(i > 10 && i<=20)
			{
				var item = Ember.Object.create({
                letter: hour+':'+i
                ,frequency: '0.10'
            	});
				
			}
			else if(i>20 && i<=40)
			{
				var item = Ember.Object.create({
                letter: hour+':'+i
                ,frequency: '0.30'
            	});
			}
			else if(i>40 && i<60)
			{
				var item = Ember.Object.create({
                letter: hour+':'+i
                ,frequency: '0.03'
            	});
				
			}
			else if(i==60)
			{
				var item = Ember.Object.create({
                letter: (hour+1)
                ,frequency: '0.5'
            	});
				
			}
			
            
            newContent[i] = item;
        }
var CHART_DATA = newContent;*/
var CHART_DATA = [
    {  "letter":"1", "frequency":0.04192 },
	{  "letter":"2", "frequency":0.04292 },
    {  "letter":"3", "frequency":0.04067 },
    {  "letter":"4", "frequency":0.03980 },
    {  "letter":"5", "frequency":0.03853 },
    {  "letter":"6", "frequency":0.03702 },
    {  "letter":"7", "frequency":0.03688 },
    {  "letter":"8", "frequency":0.03522 },
    {  "letter":"9", "frequency":0.03494 },
    {  "letter":"10", "frequency":0.03397 },
    {  "letter":"11", "frequency":0.03253 },
    {  "letter":"12", "frequency":0.03147 },
	{  "letter":"13", "frequency":0.03092 },
	{  "letter":"14", "frequency":0.02992 },
    {  "letter":"15", "frequency":0.02867 },
    {  "letter":"16", "frequency":0.02780 },
    {  "letter":"17", "frequency":0.02653 },
    {  "letter":"18", "frequency":0.02602 },
    {  "letter":"19", "frequency":0.02688 },
    {  "letter":"20", "frequency":0.02622 },
    {  "letter":"21", "frequency":0.02794 },
    {  "letter":"22", "frequency":0.02873 },
    {  "letter":"23", "frequency":0.02953 },
    {  "letter":"24", "frequency":0.03047 },
	{  "letter":"26", "frequency":0.03192 },
	{  "letter":"27", "frequency":0.03292 },
    {  "letter":"28", "frequency":0.03367 },
    {  "letter":"29", "frequency":0.03480 },
    {  "letter":"30", "frequency":0.03553 },
    {  "letter":"31", "frequency":0.03602 },
    {  "letter":"32", "frequency":0.03788 },
    {  "letter":"33", "frequency":0.038022 },
    {  "letter":"34", "frequency":0.03994 },
    {  "letter":"35", "frequency":0.04073 },
    {  "letter":"36", "frequency":0.04153 },
    {  "letter":"37", "frequency":0.04247 },
	{  "letter":"38", "frequency":0.04347 },
	{  "letter":"39", "frequency":0.04453 },
	{  "letter":"40", "frequency":0.04573 },
	{  "letter":"41", "frequency":0.04692 },
	{  "letter":"42", "frequency":0.04792 },
    {  "letter":"43", "frequency":0.04867 },
    {  "letter":"44", "frequency":0.04980 },
    {  "letter":"45", "frequency":0.05053 },
    {  "letter":"46", "frequency":0.051702 },
    {  "letter":"47", "frequency":0.05388 },
    {  "letter":"48", "frequency":0.05622 },
    {  "letter":"49", "frequency":0.05894 },
	{  "letter":"50", "frequency":0.06094 },
	{  "letter":"51", "frequency":0.06292 },
	{  "letter":"52", "frequency":0.06492 },
    {  "letter":"53", "frequency":0.06567 },
    {  "letter":"54", "frequency":0.06680 },
    {  "letter":"55", "frequency":0.06683 },
    {  "letter":"56", "frequency":0.06502 },
    {  "letter":"57", "frequency":0.06488 },
    {  "letter":"58", "frequency":0.06422 },
    {  "letter":"59", "frequency":0.06422 },
	{  "letter":"60", "frequency":0.06294 },
	{  "letter":"61", "frequency":0.06192 },
	{  "letter":"62", "frequency":0.06092 },
    {  "letter":"63", "frequency":0.05967 },
    {  "letter":"64", "frequency":0.05880 },
    {  "letter":"65", "frequency":0.05753 },
    {  "letter":"66", "frequency":0.05602 },
    {  "letter":"67", "frequency":0.05588 },
    {  "letter":"68", "frequency":0.05422 },
    {  "letter":"69", "frequency":0.05394 },
	{  "letter":"70", "frequency":0.05294 },
	{  "letter":"71", "frequency":0.05192 },
	{  "letter":"72", "frequency":0.05092 },
    {  "letter":"73", "frequency":0.05067 },
    {  "letter":"74", "frequency":0.05180 },
    {  "letter":"75", "frequency":0.05153 },
    {  "letter":"76", "frequency":0.05002 },
    {  "letter":"77", "frequency":0.04988 },
    {  "letter":"78", "frequency":0.04822 },
    {  "letter":"79", "frequency":0.04794 },
	{  "letter":"80", "frequency":0.04694 }
    
    
    
  ];