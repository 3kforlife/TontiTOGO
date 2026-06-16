import 'package:flutter/material.dart';
import '../constants/app_constants.dart';

class AppCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final double? width;
  final double? height;
  final Color? backgroundColor;
  final Gradient? gradient;
  final List<BoxShadow>? boxShadow;
  final VoidCallback? onTap;

  const AppCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(AppSpacing.lg),
    this.margin,
    this.width,
    this.height,
    this.backgroundColor,
    this.gradient,
    this.boxShadow,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: width,
        height: height,
        padding: padding,
        margin: margin,
        decoration: BoxDecoration(
          color: backgroundColor ?? AppColors.white,
          gradient: gradient,
          borderRadius: BorderRadius.circular(AppBorderRadius.xxl),
          boxShadow: boxShadow ?? AppShadows.lg,
          border: Border.all(
            color: AppColors.gray100,
            width: 1,
          ),
        ),
        child: child,
      ),
    );
  }
}

class AppFeatureIcon extends StatelessWidget {
  final IconData icon;
  final Color? iconColor;
  final Color? backgroundColor;
  final double size;

  const AppFeatureIcon({
    super.key,
    required this.icon,
    this.iconColor,
    this.backgroundColor,
    this.size = 64,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        gradient: backgroundColor == null
            ? LinearGradient(
                colors: [
                  AppColors.primary.withValues(alpha: 0.15),
                  AppColors.primaryLight,
                ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              )
            : null,
        color: backgroundColor,
        borderRadius: BorderRadius.circular(size * 0.3),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.1),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Icon(
        icon,
        color: iconColor ?? AppColors.primary,
        size: size * 0.5,
      ),
    );
  }
}

class TontiTogoLogo extends StatelessWidget {
  final double size;

  const TontiTogoLogo({
    super.key,
    this.size = 100,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.primary100,
        borderRadius: BorderRadius.circular(AppBorderRadius.xl),
      ),
      child: CustomPaint(
        painter: _TontiTogoLogoPainter(),
      ),
    );
  }
}

class _TontiTogoLogoPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final scale = size.width / 40;

    // Helper to scale coordinates
    double s(double x) => x * scale;

    // Background circle
    final bgPaint = Paint()
      ..color = AppColors.primary100
      ..style = PaintingStyle.fill;
    canvas.drawCircle(Offset(s(20), s(20)), s(18), bgPaint);

    final bgStrokePaint = Paint()
      ..color = AppColors.primary
      ..style = PaintingStyle.stroke
      ..strokeWidth = s(1.5);
    canvas.drawCircle(Offset(s(20), s(20)), s(18), bgStrokePaint);

    // Connecting lines
    final linePaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = s(1.5)
      ..strokeCap = StrokeCap.round;

    // Line 1 (Top-Right: Green)
    linePaint.color = AppColors.primary;
    canvas.drawLine(Offset(s(20), s(13)), Offset(s(27), s(20)), linePaint);

    // Line 2 (Right-Bottom: Yellow)
    linePaint.color = AppColors.warning;
    canvas.drawLine(Offset(s(27), s(20)), Offset(s(20), s(27)), linePaint);

    // Line3 (Bottom-Left: Red)
    linePaint.color = AppColors.danger;
    canvas.drawLine(Offset(s(20), s(27)), Offset(s(13), s(20)), linePaint);

    // Line4 (Left-Top: Dark Green)
    linePaint.color = AppColors.primaryDark;
    canvas.drawLine(Offset(s(13), s(20)), Offset(s(20), s(13)), linePaint);

    // Person 1: Top
    _drawPerson(canvas, Offset(s(20), s(7)), AppColors.primary, 0.0, scale);
    // Person2: Right
    _drawPerson(canvas, Offset(s(33), s(20)), AppColors.warning, 90.0, scale);
    // Person3: Bottom
    _drawPerson(canvas, Offset(s(20), s(33)), AppColors.danger, 180.0, scale);
    // Person4: Left
    _drawPerson(canvas, Offset(s(7), s(20)), AppColors.primaryDark, 270.0, scale);

    // Center circle outer
    final centerOuterPaint = Paint()
      ..color = AppColors.white
      ..style = PaintingStyle.fill;
    canvas.drawCircle(Offset(s(20), s(20)), s(5.5), centerOuterPaint);

    final centerOuterStrokePaint = Paint()
      ..color = AppColors.primary
      ..style = PaintingStyle.stroke
      ..strokeWidth = s(2);
    canvas.drawCircle(Offset(s(20), s(20)), s(5.5), centerOuterStrokePaint);

    // Center dot
    final centerDotPaint = Paint()
      ..color = AppColors.primary
      ..style = PaintingStyle.fill;
    canvas.drawCircle(Offset(s(20), s(20)), s(2.5), centerDotPaint);
  }

  void _drawPerson(
      Canvas canvas, Offset center, Color color, double rotation, double scale) {
    canvas.save();
    canvas.translate(center.dx, center.dy);
    canvas.rotate(rotation * 3.141592653589793 / 180);

    final paint = Paint()..color = color;

    // Head
    canvas.drawCircle(Offset(0, 0), scale * 3.5, paint);

    // Body
    final bodyPath = Path()
      ..moveTo(-scale * 3.5, scale * 6.5)
      ..quadraticBezierTo(0, scale * 4.5, scale * 3.5, scale * 6.5)
      ..lineTo(scale * 3.5, scale * 13)
      ..quadraticBezierTo(0, scale * 15, -scale * 3.5, scale * 13)
      ..close();
    canvas.drawPath(bodyPath, paint);

    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
